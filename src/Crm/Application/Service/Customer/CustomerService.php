<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Crm\Application\Service\Customer;

use App\Crm\Transport\Event\CustomerCreateEvent;
use App\Crm\Transport\Event\CustomerCreatePostEvent;
use App\Crm\Transport\Event\CustomerCreatePreEvent;
use App\Crm\Transport\Event\CustomerMetaDefinitionEvent;
use App\Crm\Transport\Event\CustomerUpdatePostEvent;
use App\Crm\Transport\Event\CustomerUpdatePreEvent;
use App\Crm\Application\Configuration\SystemConfiguration;
use App\Crm\Application\Utils\NumberGenerator;
use App\Crm\Domain\Entity\Customer;
use App\Crm\Domain\Repository\CustomerRepository;
use App\Crm\Domain\Repository\Query\CustomerQuery;
use App\Crm\Transport\Validator\ValidationFailedException;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CustomerService
{
    public function __construct(
        private CustomerRepository $repository,
        private SystemConfiguration $configuration,
        private ValidatorInterface $validator,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    private function getDefaultTimezone(): string
    {
        if (null === ($timezone = $this->configuration->getCustomerDefaultTimezone())) {
            $timezone = date_default_timezone_get();
        }

        return $timezone;
    }

    public function createNewCustomer(string $name): Customer
    {
        $customer = new Customer($name);
        $customer->setTimezone($this->getDefaultTimezone());
        $customer->setCountry($this->configuration->getCustomerDefaultCountry());
        $customer->setCurrency($this->configuration->getCustomerDefaultCurrency());
        $customer->setNumber($this->calculateNextCustomerNumber());

        $this->dispatcher->dispatch(new CustomerMetaDefinitionEvent($customer));
        $this->dispatcher->dispatch(new CustomerCreateEvent($customer));

        return $customer;
    }

    public function saveNewCustomer(Customer $customer): Customer
    {
        if (null !== $customer->getId()) {
            throw new InvalidArgumentException('Cannot create customer, already persisted');
        }

        $this->validateCustomer($customer);

        $this->dispatcher->dispatch(new CustomerCreatePreEvent($customer));
        $this->repository->saveCustomer($customer);
        $this->dispatcher->dispatch(new CustomerCreatePostEvent($customer));

        return $customer;
    }

    /**
     * @param Customer $customer
     * @param string[] $groups
     * @throws ValidationFailedException
     */
    private function validateCustomer(Customer $customer, array $groups = []): void
    {
        $errors = $this->validator->validate($customer, null, $groups);

        if ($errors->count() > 0) {
            throw new ValidationFailedException($errors, 'Validation Failed');
        }
    }

    public function updateCustomer(Customer $customer): Customer
    {
        $this->validateCustomer($customer);

        $this->dispatcher->dispatch(new CustomerUpdatePreEvent($customer));
        $this->repository->saveCustomer($customer);
        $this->dispatcher->dispatch(new CustomerUpdatePostEvent($customer));

        return $customer;
    }

    public function findCustomerByName(string $name): ?Customer
    {
        return $this->repository->findOneBy(['name' => $name]);
    }

    public function findCustomerByNumber(string $number): ?Customer
    {
        return $this->repository->findOneBy(['number' => $number]);
    }

    /**
     * @return iterable<Customer>
     */
    public function findCustomer(CustomerQuery $query): iterable
    {
        return $this->repository->getCustomersForQuery($query);
    }

    public function countCustomer(bool $visible = true): int
    {
        return $this->repository->countCustomer($visible);
    }

    public function calculateNextCustomerNumber(): string
    {
        $format = $this->configuration->find('customer.number_format');
        if (empty($format) || !\is_string($format)) {
            $format = '{cc,4}';
        }

        $numberGenerator = new NumberGenerator($format, function (string $originalFormat, string $format, int $increaseBy): string|int {
            return match ($format) {
                'cc' => $this->repository->count([]) + $increaseBy,
                default => $originalFormat,
            };
        });

        return $numberGenerator->getNumber();
    }
}