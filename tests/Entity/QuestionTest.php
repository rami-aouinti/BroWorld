<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Answer;
use App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Quiz\Model\Entity\Question
 */
final class QuestionTest extends KernelTestCase
{
    /**
     * @covers \App\Quiz\Model\Entity\Question::getCorrectAnswerCode
     */
    public function testGetCorrectAnswerCode(): void
    {
        $question = new Question();
        $answer = (new Answer())
            ->setCode('B')
            ->setCorrect(true);
        $question->addAnswer($answer);
        self::assertSame('B', $question->getCorrectAnswerCode());
    }

    /**
     * @covers \App\Quiz\Model\Entity\Question::getCorrectAnswerCode
     */
    public function testGetCorrectAnswerCodeException(): void
    {
        try {
            $question = new Question();
            $question->getCorrectAnswerCode();
            self::fail('Calling getCorrectAnswerCode() on a question without answers raises an excpetion.');
        } catch (\Exception $e) {
            self::assertInstanceOf(\LogicException::class, $e);
        }
    }

    /**
     * @covers \App\Quiz\Model\Entity\Question::addAnswer
     * @covers \App\Quiz\Model\Entity\Question::removeAnswer
     * @covers \App\Quiz\Model\Entity\Question::getAnswers
     */
    public function testAnswersGetters(): void
    {
        $question = new Question();
        $answer = (new Answer())
            ->setCode('B')
            ->setCorrect(true);

        $question->addAnswer($answer);
        self::assertCount(1, $question->getAnswers());

        $question->removeAnswer($answer);
        self::assertCount(0, $question->getAnswers());
    }
}
