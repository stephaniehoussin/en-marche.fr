<?php

namespace Tests\AppBundle\CitizenProject;

use AppBundle\CitizenProject\CitizenProjectAuthority;
use AppBundle\CitizenProject\CitizenProjectMessageNotifier;
use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CitizenProject;
use AppBundle\Membership\AdherentEmailSubscription;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 * @group citizenProject
 */
class CitizenProjectManagerTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /* @var CitizenProjectManager */
    private $citizenProjectManager;

    public function testGetCitizenProjectAdministrators()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $administrators = $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID))
        );

        // Approved citizen projects
        $this->assertCount(2, $administrators);
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID)));

        // Unapproved citizen projects
        $this->assertCount(0, $this->citizenProjectManager->getCitizenProjectAdministrators($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testGetCitizenProjectFollowers()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $followers = $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID))
        );

        // Approved citizen projects
        $this->assertCount(2, $followers);
        $this->assertCount(0, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_3_UUID)));
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_4_UUID)));
        $this->assertCount(2, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_5_UUID)));

        // Unapproved citizen projects
        $this->assertCount(1, $this->citizenProjectManager->getCitizenProjectFollowers($this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_2_UUID)));
    }

    public function testFindAdherentNearCitizenProjectOrAcceptAllNotification()
    {
        $citizenProject = $this->getCitizenProject(LoadCitizenProjectData::CITIZEN_PROJECT_1_UUID);
        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject);

        $this->assertSame(4, $adherents->count());

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, false);

        $this->assertSame(5, $adherents->count());

        $adherent = $this->getAdherentRepository()->findOneByEmail('francis.brioul@yahoo.com');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(AdherentEmailSubscription::DISTANCE_100KM);

        $adherent = $this->getAdherentRepository()->findOneByEmail('referent@en-marche-dev.fr');
        $adherent->setCitizenProjectCreationEmailSubscriptionRadius(AdherentEmailSubscription::DISTANCE_100KM);

        $this->getManagerRegistry()->getManager()->flush();
        $this->getManagerRegistry()->getManager()->clear();

        $adherents = $this->citizenProjectManager->findAdherentNearCitizenProjectOrAcceptAllNotification($citizenProject, 0, true, CitizenProjectMessageNotifier::RADIUS_NOTIFICATION_NEAR_PROJECT_CITIZEN);

        $this->assertSame(6, $adherents->count());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ]);

        $this->container = $this->getContainer();
        $this->citizenProjectManager = new CitizenProjectManager(
            $this->getManagerRegistry(),
            $this->getStorage(),
            $this->createMock(CitizenProjectAuthority::class)
        );
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->citizenProjectManager = null;

        parent::tearDown();
    }
}
