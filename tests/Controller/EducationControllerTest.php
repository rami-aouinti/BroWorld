<?php

namespace App\Test\Controller;

use App\Resume\Model\Entity\Education;
use App\Resume\Model\Repository\EducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EducationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EducationRepository $repository;
    private string $path = '/education/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Education::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Education index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'education[start]' => 'Testing',
            'education[end]' => 'Testing',
            'education[school]' => 'Testing',
            'education[city]' => 'Testing',
            'education[description]' => 'Testing',
            'education[diploma]' => 'Testing',
            'education[specality]' => 'Testing',
            'education[user]' => 'Testing',
        ]);

        self::assertResponseRedirects('/education/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Education();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setSchool('My Title');
        $fixture->setCity('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDiploma('My Title');
        $fixture->setSpecality('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Education');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Education();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setSchool('My Title');
        $fixture->setCity('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDiploma('My Title');
        $fixture->setSpecality('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'education[start]' => 'Something New',
            'education[end]' => 'Something New',
            'education[school]' => 'Something New',
            'education[city]' => 'Something New',
            'education[description]' => 'Something New',
            'education[diploma]' => 'Something New',
            'education[specality]' => 'Something New',
            'education[user]' => 'Something New',
        ]);

        self::assertResponseRedirects('/education/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getStart());
        self::assertSame('Something New', $fixture[0]->getEnd());
        self::assertSame('Something New', $fixture[0]->getSchool());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDiploma());
        self::assertSame('Something New', $fixture[0]->getSpecality());
        self::assertSame('Something New', $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Education();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setSchool('My Title');
        $fixture->setCity('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDiploma('My Title');
        $fixture->setSpecality('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/education/');
    }
}
