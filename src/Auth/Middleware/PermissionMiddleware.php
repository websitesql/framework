<?php

namespace WebsiteSQL\Framework\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WebsiteSQL\Framework\Core\App;
use WebsiteSQL\Framework\Exceptions\PermissionDeniedException;

class PermissionMiddleware implements MiddlewareInterface
{
    /**
     * This object holds the App container instance
     * 
     * @var App
     */
    private App $app;

    /**
     * The required permission for the route
     * 
     * @var string|null
     */
    private ?string $requiredPermission;

    /**
     * Stores the permission filter after checking permissions
     * 
     * @var array|null
     */
    private ?array $permissionFilter = null;

	/**
	 * Stores the user ID for authenticated users
	 * 
	 * @var int|null
	 */
	private ?int $userId = null;

    /**
     * Constructor
     * 
     * @param App $app
     * @param string|null $permission The required permission for the route
     */
    public function __construct(App $app, ?string $permission = null)
    {
        $this->app = $app;
        $this->requiredPermission = $permission;
    }

    /**
     * Process the middleware
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws PermissionDeniedException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // If no permission is required, continue with the request
        if (empty($this->requiredPermission)) {
            return $handler->handle($request);
        }
        
        // Check if user has the required permission
        if (!$this->hasPermission($request, $this->requiredPermission)) {
            throw new PermissionDeniedException();
        }
        
        // Add permission filter to the request
		if (!empty($this->permissionFilter)) {
			$request = $request->withAttribute('permission_filter', $this->permissionFilter);
		}
        
        // User has permission, proceed with the request
        return $handler->handle($request);
    }

    /**
     * Check if the request is authenticated
     * 
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isAuthenticated(ServerRequestInterface $request): bool
    {
        $user = $request->getAttribute('user');
		$this->userId = $user['id'] ?? null;
        return !empty($user);
    }

    /**
     * Get the user's role ID from the request
     * 
     * @param ServerRequestInterface $request
     * @return int|null
     */
    protected function getUserRoleId(ServerRequestInterface $request): ?int
    {
        $user = $request->getAttribute('user');
        return $user['role'] ?? null;
    }

    /**
     * Check if the user has the specified permission
     * 
     * @param ServerRequestInterface $request
     * @param string $permission The permission to check
     * @return bool
     */
    protected function hasPermission(ServerRequestInterface $request, string $permission): bool
    {
        $isAuthenticated = $this->isAuthenticated($request);
        
        if ($isAuthenticated) {
            // Get user's role ID
            $roleId = $this->getUserRoleId($request);
            
            if ($roleId) {
                // Check if the role is an administrator
                $role = $this->app->getDatabase()->get(
                    $this->app->getStrings()->getTableRoles(),
                    ['administrator'],
                    ['id' => $roleId]
                );
                
                // If role is administrator, allow access to everything
                if ($role && isset($role['administrator']) && $role['administrator']) {
                    return true;
                }
                
                // Check if the role has the specific permission
                return $this->checkRolePermission($roleId, $permission);
            }
        }
        
        // For unauthenticated users, check public roles
        return $this->checkPublicRolesPermission($permission);
    }

    /**
     * Check if a specific role has the given permission
     * 
     * @param int $roleId
     * @param string $permission
     * @return bool
     */
    protected function checkRolePermission(int $roleId, string $permission): bool
    {
        // Check if the role has the specific permission and get filter in one query
        $permissionRecord = $this->app->getDatabase()->get(
            $this->app->getStrings()->getTablePermissions(),
            [
				'enabled',
				'filter'
			],
            [
                'role' => $roleId,
                'name' => $permission
            ]
        );
        
        // Store the filter if it exists
        if ($permissionRecord && isset($permissionRecord['filter']) && !empty($permissionRecord['filter'])) {
            $this->permissionFilter = unserialize($permissionRecord['filter']);

			// If a user is authenticated, Search and replace {{$USERID}} with the actual user ID
			if ($this->userId) {
				$this->permissionFilter = str_replace('{{$USERID}}', $this->userId, $this->permissionFilter);
			}
		}
        
        return $permissionRecord && isset($permissionRecord['enabled']) && $permissionRecord['enabled'];
    }

    /**
     * Check if any public role has the given permission
     * 
     * @param string $permission
     * @return bool
     */
    protected function checkPublicRolesPermission(string $permission): bool
    {
        // Get all public access roles
        $publicRoles = $this->app->getDatabase()->select(
            $this->app->getStrings()->getTableRoles(),
            ['id'],
            ['public_access' => 1]
        );
        
        if (empty($publicRoles)) {
            return false;
        }
        
        // Check if any public role has the permission
        foreach ($publicRoles as $role) {
            if ($this->checkRolePermission($role['id'], $permission)) {
                return true;
            }
        }
        
        return false;
    }
}
