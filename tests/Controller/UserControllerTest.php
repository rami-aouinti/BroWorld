<?php

namespace App\Test\Controller;

use App\User\Model\Entity\User;
use App\User\Model\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $repository;
    private string $path = '/user/';
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(User::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

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
            'user[email]' => 'Testing',
            'user[roles]' => 'Testing',
            'user[password]' => 'Testing',
            'user[isVerified]' => 'Testing',
            'user[firstName]' => 'Testing',
            'user[lastName]' => 'Testing',
            'user[description]' => 'Testing',
            'user[nationality]' => 'Testing',
            'user[country]' => 'Testing',
            'user[state]' => 'Testing',
            'user[city]' => 'Testing',
            'user[street]' => 'Testing',
            'user[housnumber]' => 'Testing',
            'user[birthday]' => 'Testing',
            'user[position]' => 'Testing',
            'user[photo]' => 'Testing',
            'user[phone]' => 'Testing',
            'user[googleId]' => 'Testing',
            'user[facebookId]' => 'Testing',
            'user[hostedDomain]' => 'Testing',
            'user[avatar]' => 'Testing',
        ]);

        self::assertResponseRedirects('/user/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setIsVerified('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setLastName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setNationality('My Title');
        $fixture->setCountry('My Title');
        $fixture->setState('My Title');
        $fixture->setCity('My Title');
        $fixture->setStreet('My Title');
        $fixture->setHousnumber('My Title');
        $fixture->setBirthday('My Title');
        $fixture->setPosition('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setPhone('My Title');
        $fixture->setGoogleId('My Title');
        $fixture->setFacebookId('My Title');
        $fixture->setHostedDomain('My Title');
        $fixture->setAvatar('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setIsVerified('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setLastName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setNationality('My Title');
        $fixture->setCountry('My Title');
        $fixture->setState('My Title');
        $fixture->setCity('My Title');
        $fixture->setStreet('My Title');
        $fixture->setHousnumber('My Title');
        $fixture->setBirthday('My Title');
        $fixture->setPosition('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setPhone('My Title');
        $fixture->setGoogleId('My Title');
        $fixture->setFacebookId('My Title');
        $fixture->setHostedDomain('My Title');
        $fixture->setAvatar('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'user[email]' => 'Something New',
            'user[roles]' => 'Something New',
            'user[password]' => 'Something New',
            'user[isVerified]' => 'Something New',
            'user[firstName]' => 'Something New',
            'user[lastName]' => 'Something New',
            'user[description]' => 'Something New',
            'user[nationality]' => 'Something New',
            'user[country]' => 'Something New',
            'user[state]' => 'Something New',
            'user[city]' => 'Something New',
            'user[street]' => 'Something New',
            'user[housnumber]' => 'Something New',
            'user[birthday]' => 'Something New',
            'user[position]' => 'Something New',
            'user[photo]' => 'Something New',
            'user[phone]' => 'Something New',
            'user[googleId]' => 'Something New',
            'user[facebookId]' => 'Something New',
            'user[hostedDomain]' => 'Something New',
            'user[avatar]' => 'Something New',
        ]);

        self::assertResponseRedirects('/user/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getRoles());
        self::assertSame('Something New', $fixture[0]->getPassword());
        self::assertSame('Something New', $fixture[0]->getIsVerified());
        self::assertSame('Something New', $fixture[0]->getFirstName());
        self::assertSame('Something New', $fixture[0]->getLastName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getNationality());
        self::assertSame('Something New', $fixture[0]->getCountry());
        self::assertSame('Something New', $fixture[0]->getState());
        self::assertSame('Something New', $fixture[0]->getCity());
        self::assertSame('Something New', $fixture[0]->getStreet());
        self::assertSame('Something New', $fixture[0]->getHousnumber());
        self::assertSame('Something New', $fixture[0]->getBirthday());
        self::assertSame('Something New', $fixture[0]->getPosition());
        self::assertSame('Something New', $fixture[0]->getPhoto());
        self::assertSame('Something New', $fixture[0]->getPhone());
        self::assertSame('Something New', $fixture[0]->getGoogleId());
        self::assertSame('Something New', $fixture[0]->getFacebookId());
        self::assertSame('Something New', $fixture[0]->getHostedDomain());
        self::assertSame('Something New', $fixture[0]->getAvatar());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new User();
        $fixture->setEmail('My Title');
        $fixture->setRoles('My Title');
        $fixture->setPassword('My Title');
        $fixture->setIsVerified('My Title');
        $fixture->setFirstName('My Title');
        $fixture->setLastName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setNationality('My Title');
        $fixture->setCountry('My Title');
        $fixture->setState('My Title');
        $fixture->setCity('My Title');
        $fixture->setStreet('My Title');
        $fixture->setHousnumber('My Title');
        $fixture->setBirthday('My Title');
        $fixture->setPosition('My Title');
        $fixture->setPhoto('My Title');
        $fixture->setPhone('My Title');
        $fixture->setGoogleId('My Title');
        $fixture->setFacebookId('My Title');
        $fixture->setHostedDomain('My Title');
        $fixture->setAvatar('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/user/');
    }
}
