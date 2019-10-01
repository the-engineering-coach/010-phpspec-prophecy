<?php

namespace spec\Braddle;

use Braddle\DrivingLicenceGenerator;
use Braddle\InvalidApplicationException;
use Braddle\LicenceApplicant;
use Braddle\RandomNumbersGenerator;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class DrivingLicenceGeneratorSpec extends ObjectBehavior
{

    function let(LoggerInterface $logger, RandomNumbersGenerator $generator)
    {
        $this->beConstructedWith($logger, $generator);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DrivingLicenceGenerator::class);
    }

    function it_does_not_allow_underage_applicant_to_get_a_licence(LicenceApplicant $applicant, LoggerInterface $logger)
    {
        $applicant->getAge()->willReturn(16);
        $applicant->getId()->willReturn(12);

        $this->shouldThrow(
            InvalidApplicationException::class
        )->duringGenerate($applicant);

        $logger->error("Underage Applicant: 12")
            ->shouldHaveBeenCalledOnce();
    }

    function it_does_not_allow_applicants_to_have_more_than_one_licence(LicenceApplicant $applicant, LoggerInterface $logger)
    {
        $applicant->getAge()->willReturn(18);
        $applicant->getId()->willReturn(12);
        $applicant->holdsLicence()->willReturn(true);

        $this->shouldThrow(
            new InvalidApplicationException("Duplicate Applicant")
        )->duringGenerate($applicant);

        $logger->error("Duplicate Applicant: 12")
            ->shouldHaveBeenCalledOnce();
    }

    function it_should_create_a_driving_licence_number_for_a_valid_applicant(LicenceApplicant $applicant, RandomNumbersGenerator $generator)
    {
        $applicant->getAge()->willReturn(18);
        $applicant->holdsLicence()->willReturn(false);
        $applicant->getInitials()->willReturn("MDB");
        $applicant->getDateOfBirth()->willReturn(new \DateTime("11-07-1999 00:00:00"));

        $generator->generate(3)->willReturn("123");

        $this->generate($applicant)->shouldReturn("MDB11071999123");
    }

    function it_should_pad_licence_number_to_14_characters_minimum(LicenceApplicant $applicant1, LicenceApplicant $applicant2, LicenceApplicant $applicant4, RandomNumbersGenerator $generator)
    {
        $applicant1->getAge()->willReturn(18);
        $applicant1->holdsLicence()->willReturn(false);
        $applicant1->getInitials()->willReturn("M");
        $applicant1->getDateOfBirth()->willReturn(new \DateTime("11-07-1999 00:00:00"));

        $applicant2->getAge()->willReturn(18);
        $applicant2->holdsLicence()->willReturn(false);
        $applicant2->getInitials()->willReturn("MD");
        $applicant2->getDateOfBirth()->willReturn(new \DateTime("11-07-1999 00:00:00"));

        $applicant4->getAge()->willReturn(18);
        $applicant4->holdsLicence()->willReturn(false);
        $applicant4->getInitials()->willReturn("MDBB");
        $applicant4->getDateOfBirth()->willReturn(new \DateTime("11-07-1999 00:00:00"));

        $generator->generate(3)->willReturn("123");
        $generator->generate(4)->willReturn("1234");
        $generator->generate(5)->willReturn("12345");

        $this->generate($applicant4)->shouldReturn("MDBB11071999123");
        $this->generate($applicant2)->shouldReturn("MD110719991234");
        $this->generate($applicant1)->shouldReturn("M1107199912345");

    }
}
