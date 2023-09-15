<?php

namespace App\Test\Controller;

use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Repository\ExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ExperienceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ExperienceRepository $repository;
    private string $path = '/experience/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Experience::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Experience index');

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
            'experience[start]' => 'Testing',
            'experience[end]' => 'Testing',
            'experience[job]' => 'Testing',
            'experience[description]' => 'Testing',
            'experience[company]' => 'Testing',
            'experience[city]' => 'Testing',
            'experience[user]' => 'Testing',
        ]);

        self::assertResponseRedirects('/experience/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Experience();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setJob('My Title');
        $fixture->setDescription('My Title');
        $fixture->setCompany('My Title');
        $fixture->setCity('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Experience');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Experience();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setJob('My Title');
        $fixture->setDescription('My Title');
        $fixture->setCompany('My Title');
        $fixture->setCity('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'experience[start]' => 'Something New',
            'experience[end]' => 'Something New',
            'experience[job]' => 'Something New',
            'experience[description]' => 'Something New',
            'experience[company]' => 'Something New',
            'experience[city]' => 'Something New',
            'experience[user]' => 'Something New',
        ]);

        self::assertResponseRedirects('/experience/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getStart());
        self::assertSame('Something New', $fixture[0]->getEnd());
        self::assertSame('Something New', $fixture[0]->getJob());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getCompany());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Experience();
        $fixture->setStart('My Title');
        $fixture->setEnd('My Title');
        $fixture->setJob('My Title');
        $fixture->setDescription('My Title');
        $fixture->setCompany('My Title');
        $fixture->setCity('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/experience/');
    }
}
