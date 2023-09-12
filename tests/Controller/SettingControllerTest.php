<?php

namespace App\Test\Controller;

use App\Frontend\Model\Entity\Setting;
use App\Frontend\Model\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SettingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private SettingRepository $repository;
    private string $path = '/setting/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Setting::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Setting index');

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
            'setting[sidebar]' => 'Testing',
            'setting[sidenav]' => 'Testing',
            'setting[navbar]' => 'Testing',
            'setting[user]' => 'Testing',
        ]);

        self::assertResponseRedirects('/setting/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Setting();
        $fixture->setSidebar('My Title');
        $fixture->setSidenav('My Title');
        $fixture->setNavbar('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Setting');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Setting();
        $fixture->setSidebar('My Title');
        $fixture->setSidenav('My Title');
        $fixture->setNavbar('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'setting[sidebar]' => 'Something New',
            'setting[sidenav]' => 'Something New',
            'setting[navbar]' => 'Something New',
            'setting[user]' => 'Something New',
        ]);

        self::assertResponseRedirects('/setting/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getSidebar());
        self::assertSame('Something New', $fixture[0]->getSidenav());
        self::assertSame('Something New', $fixture[0]->getNavbar());
        self::assertSame('Something New', $fixture[0]->getUser());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Setting();
        $fixture->setSidebar('My Title');
        $fixture->setSidenav('My Title');
        $fixture->setNavbar('My Title');
        $fixture->setUser('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/setting/');
    }
}
