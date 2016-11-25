<?php
namespace Blast\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ChoiceController extends Controller
{
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function addChoiceAction(Request $request)
    {
        $manager = $this->getDoctrine()->getManager();
        $class = $request->get('class');
        if ($class === null)
            throw new \Exception('"class" parameter not sent');
        $field = $request->get('field');
        if ($field === null)
            throw new \Exception('"field" parameter not sent');
        $value = $request->get('value');
        if ($value === null)
            throw new \Exception('"value" parameter not sent');

        $choice = new $class();
        $choice->setLabel($field);
        $choice->setValue($value);
        $manager->persist($choice);
        $manager->flush();

        return new JsonResponse(array(
            'name'  => $choice->getLabel(),
            'value' => $choice->getValue(),
            'id'    => $choice->getId()
            )
        );
    }
}