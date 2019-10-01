<?php

namespace Braddle;

use Psr\Log\LoggerInterface;

class DrivingLicenceGenerator
{
    private $logger;
    private $generator;

    public function __construct(LoggerInterface $logger, RandomNumbersGenerator $generator)
    {
        $this->logger = $logger;
        $this->generator = $generator;
    }

    public function generate(LicenceApplicant $applicant)
    {
        if ($applicant->getAge() < 17) {
            $this->logger->error("Underage Applicant: " . $applicant->getId());
            throw new InvalidApplicationException(
                "Underage Applicant"
            );
        }

        if ($applicant->holdsLicence()) {
            $this->logger->error("Duplicate Applicant: " . $applicant->getId());
            throw new InvalidApplicationException(
                "Duplicate Applicant"
            );
        }

        $licence =  $applicant->getInitials() .
            $applicant->getDateOfBirth()->format("dmY");

        $padding = strlen($licence) < 11 ? 14 - strlen($licence) : 3;

        return $licence . $this->generator->generate($padding);
    }
}
