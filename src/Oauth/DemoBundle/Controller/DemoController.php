<?php

namespace Oauth\DemoBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Oauth\DemoBundle\Entity\Demo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DemoController extends Controller
{
    /**
     * @Rest\View
     * @ApiDoc(
     *  description="Returns a collection of Demo"
     * )
     */
    public function allAction()
    {
        $demos = $this->getDoctrine()
            ->getRepository('OauthDemoBundle:Demo')
            ->findAll();

        return array('demos' => $demos);
    }

    /**
     * @Rest\View
     * @ApiDoc(
     *  description="Return a Demo",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="id"}
     *  }
     * )
     */
    public function getAction($id)
    {
        $demo  = $this->getDoctrine()
            ->getRepository('OauthDemoBundle:Demo')
            ->find($id);


        if (!$demo instanceof Demo) {
            throw new HttpException(404, 'Demo not found');
        }

        return array('demo' => $demo);
    }

    /**
     * @param Demo $demo
     * @param Request $request
     * @return View|Response
     * @ApiDoc(
     * description="Create a new demo"
     * )
     * @ParamConverter("demo", class="Oauth\DemoBundle\Entity\Demo", converter="fos_rest.request_body")
     */
    public function newAction(Demo $demo, Request $request)
    {
        return $this->processForm($demo, $request);
    }

    /**
     * @param Demo $demo
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return View|Response
     */
    private function processForm(Demo $demo = null, Request $request)
    {
        $statusCode = 204;
        if ($demo->getId() == null)
        {
            $statusCode = 201;
        }

        if (sizeof($request->get('validationErrors')) == 0) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($demo);
            $em->flush();

            $response = new Response();
            $response->setStatusCode($statusCode);

            // set the `Location` header only when creating new resources
            if (201 === $statusCode) {
                $response->headers->set('Location',
                    $this->generateUrl(
                        'demo_demo_get', array('id' => $demo->getId()),
                        true // absolute
                    )
                );
            }

            return $response;
        }

        return View::create($request->get('validationErrors'), 400);
    }

    /**
     * @param Demo $demo
     * @param Request $request
     * @return View|Response
     * @ApiDoc(
     *  description="Edit a demo"
     * )
     * @ParamConverter("demo", class="Oauth\DemoBundle\Entity\Demo", converter="fos_rest.request_body")
     */
    public function editAction(Demo $demo, Request $request)
    {
        $dm  = $this->getDoctrine()
            ->getRepository('OauthDemoBundle:Demo')
            ->find((int)$request->get('id'));
        $dm->setDescription($demo->getDescription());
        $dm->setTitle($demo->getTitle());

        return $this->processForm($dm, $request);
    }

    /**
     * @Rest\View(statusCode=204)
     * @ApiDoc(
     * description="Delete a demo"
     * )
     */
    public function removeAction(Demo $demo)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($demo);
        $em->flush();
    }
//curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"user":{"title":"foo", "description": "bar"}}' http://api.localhost:8000/demos
}