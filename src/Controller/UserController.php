<?php

namespace App\Controller;

use App\Services\ApiResponderService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

/**
 * Class UserController
 *
 * @Route("/api/user")
 */
class UserController extends AbstractFOSRestController
{

    /**
     * 
     * Get entire system roles
     *
     * @Rest\Get("/roles", name="roles")
     * @IsGranted("ROLE_ADMIN", statusCode=403, message="Access Denied")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Roles information succeSeasonService.phpssfully given"
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="Uncaught exception",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *         @SWG\Property(property="error", type="boolean"),
     *     )
     * )
     */
    public function getRolesAction(Request $request, RoleHierarchyInterface $rh, ApiResponderService $apiResponder)
    {
        $code = 200;
        $error = false;

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $rh
        ];

        return $apiResponder->getResponse($response, 200);
    }

}
