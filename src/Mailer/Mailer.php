<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Mailer;

use WebsiteSQL\Framework\Mailer\Exception\ConfigurationException;
use WebsiteSQL\Framework\Mailer\Exception\ContentException;
use WebsiteSQL\Framework\Mailer\Exception\SendException;
use WebsiteSQL\Framework\Mailer\Exception\SmtpException;
use WebsiteSQL\Framework\Mailer\Exception\TemplateException;
use League\Plates\Engine;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Exception;

class Mailer
{
    /**
     * Configuration options
     */
    private array $options;
    
    /**
     * Email properties
     */
    private ?string $subject = null;
    private ?string $htmlContent = null;
    private ?string $plainContent = null;
    private ?string $recipient = null;
    
    /**
     * Constructor
     * 
     * @param array $options Configuration options for mail
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }
    
    /**
     * Set the template to use for email
     * 
     * @param string $template Template name
     * @param array $data Data to pass to the template
     * @return self
     * @throws ConfigurationException If template path is not set
     * @throws TemplateException If template rendering fails
     */
    public function template(string $template, array $data = []): self
    {
        if (!isset($this->options['template_path'])) {
            throw new ConfigurationException('Template path is not configured');
        }
        
        try {
            $engine = new Engine($this->options['template_path']);
            $this->htmlContent = $engine->render($template, $data);
            return $this;
        } catch (Exception $e) {
            throw new TemplateException('Failed to render template: ' . $e->getMessage());
        }
    }
    
    /**
     * Set HTML content for the email
     * 
     * @param string $html HTML content
     * @return self
     */
    public function html(string $html): self
    {
        $this->htmlContent = $html;
        return $this;
    }
    
    /**
     * Set plain text content for the email
     * 
     * @param string $text Plain text content
     * @return self
     */
    public function plain(string $text): self
    {
        $this->plainContent = $text;
        return $this;
    }
    
    /**
     * Set the subject of the email
     * 
     * @param string $subject Email subject
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    
    /**
     * Send the email to the specified recipient
     * 
     * @param string $to Email recipient
     * @return bool Success status
     * @throws ContentException If required fields are not set
     * @throws ConfigurationException If mail driver is not configured
     * @throws SendException If email fails to send
     */
    public function send(string $to): bool
    {
        $this->recipient = $to;
        
		// Validate email address
		if (!filter_var($this->recipient, FILTER_VALIDATE_EMAIL)) {
			throw new ContentException('Invalid email address: ' . $this->recipient);
		}

        // Validate required fields
        if ($this->subject === null) {
            throw new ContentException('Email subject is required');
        }
        
        if ($this->htmlContent === null && $this->plainContent === null) {
            throw new ContentException('Email content is required (HTML or plain text)');
        }
        
        // Determine which driver to use
        if (!isset($this->options['driver'])) {
            throw new ConfigurationException('Mail driver is not configured');
        }
        
        try {
            switch ($this->options['driver']) {
                case 'mail':
                    return $this->sendWithMail();
                case 'smtp':
                    return $this->sendWithSmtp();
                case 'log':
                    return $this->sendWithLog();
                default:
                    throw new ConfigurationException('Invalid mail driver: ' . $this->options['driver']);
            }
        } catch (Exception $e) {
            error_log('Mail error: ' . $e->getMessage());
            throw new SendException('Failed to send email: ' . $e->getMessage());
        }
    }
    
    /**
     * Send using PHP's mail() function
     */
    private function sendWithMail(): bool
    {
        if (!isset($this->options['from'])) {
            throw new ConfigurationException('From email is not configured');
        }
        
        if (!isset($this->options['from_name'])) {
            throw new ConfigurationException('From name is not configured');
        }
        
        $headers = "From: {$this->options['from_name']} <{$this->options['from']}>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        // Handle multi-part emails
        if ($this->htmlContent !== null && $this->plainContent !== null) {
            $boundary = md5((string)time());
            $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
            
            $message = "--$boundary\r\n";
            $message .= "Content-Type: text/plain; charset=utf-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($this->plainContent)) . "\r\n";
            
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=utf-8\r\n";
            $message .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $message .= chunk_split(base64_encode($this->htmlContent)) . "\r\n";
            
            $message .= "--$boundary--\r\n";
        } elseif ($this->htmlContent !== null) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message = $this->htmlContent;
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message = $this->plainContent;
        }
        
        if (!mail($this->recipient, $this->subject, $message, $headers)) {
            throw new SendException('Failed to send email via mail()');
        }
        
        return true;
    }
    
    /**
     * Send using SMTP
     */
    private function sendWithSmtp(): bool
    {
        // Validate SMTP configuration
        $requiredFields = ['from', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password'];
        foreach ($requiredFields as $field) {
            if (!isset($this->options[$field])) {
                throw new ConfigurationException("SMTP configuration missing: $field");
            }
        }
        
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            
            if (isset($this->options['debug']) && $this->options['debug']) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            $mail->Host = $this->options['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->options['smtp_username'];
            $mail->Password = $this->options['smtp_password'];
            $mail->Port = $this->options['smtp_port'];
            
            // Set encryption based on port
            switch ($this->options['smtp_port']) {
                case 465:
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                case 587:
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    break;
            }
            
            // Set from address
            $mail->setFrom(
                $this->options['from'],
                $this->options['from_name'] ?? 'Application'
            );
            
            // Add recipient
            $mail->addAddress($this->recipient);
            
            // Set subject
            $mail->Subject = $this->subject;
            
            // Set content
            if ($this->htmlContent !== null) {
                $mail->isHTML(true);
                $mail->Body = $this->htmlContent;
                
                if ($this->plainContent !== null) {
                    $mail->AltBody = $this->plainContent;
                }
            } else {
                $mail->isHTML(false);
                $mail->Body = $this->plainContent;
            }
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            throw new SmtpException('SMTP error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send by logging to a file
     */
    private function sendWithLog(): bool
    {
        $logContent = "To: {$this->recipient}\n";
        $logContent .= "Subject: {$this->subject}\n";
        
        if ($this->htmlContent !== null) {
            $logContent .= "HTML Content: {$this->htmlContent}\n";
        }
        
        if ($this->plainContent !== null) {
            $logContent .= "Plain Content: {$this->plainContent}\n";
        }
        
        $logContent .= "\n";
        
        if (!error_log($logContent)) {
            throw new SendException('Failed to log email');
        }
        
        return true;
    }
}