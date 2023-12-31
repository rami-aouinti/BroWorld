<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller;

use App\Frontend\Application\Data\QuestionData;
use App\Quiz\Transport\Form\QuizRestoreType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @see AppControllerTest
 */
class AppController extends AbstractController
{
    public function __construct(
        private QuestionData $questionData
    ) {
    }

    /**
     * @Route("/quiz", name="app_quiz")
     */
    public function index(Request $request): Response
    {
        $form = $this->createForm(QuizRestoreType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{uuid: string} $formData */
            $formData = $form->getData();

            return $this->redirectToRoute('quiz_question', ['uuid' => $formData['uuid']]);
        }

        return $this->render('quiz/index.html.twig', [
            'count' => $this->questionData->count(),
            'last' => $this->questionData->getLastQuestion(),
            'form' => $form->createView(),
        ]);
    }
}
