<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Command;

/*
 * This file is part of the Flowpack.SiteKickstarter package.
 */

use Flowpack\SiteKickstarter\Domain\Generator\Fusion\InheritedFusionRendererGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\GeneratorInterface;
use Flowpack\SiteKickstarter\Domain\Generator\NodeType\NodetypeConfigurationGenerator;
use Flowpack\SiteKickstarter\Domain\Modification\FileContentModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecificationFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\Package\PackageManager;
use Flowpack\SiteKickstarter\Domain\Generator\Fusion\ContentFusionRendererGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\Fusion\DocumentFusionRendererGenerator;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationCollection;

/**
 * @Flow\Scope("singleton")
 */
class WelcomeCommandController extends CommandController
{

    public function indexCommand(): void
    {
        $this->output(
            <<<EOT
            <info>
                ....######          .######
                .....#######      ...######
                .......#######   ....######
                .........####### ....######
                ....#......#######...######
                ....##.......#######.######
                ....#####......############
                ....#####  ......##########
                ....#####    ......########
                ....#####      ......######
                .#######         ........

            Welcome to Neos.
            </info>

            The following cli commands will help you to configure your Neos:

            1. Configure the database connection.
               <info>./flow setup:database</info>
            2. Migrate the database
               <info>./flow doctrine:migrate</info>
            3. Configure the imageHandling.
               <info>./flow setup:imagehandler</info>
            4. Create an admin user
               <info>./flow user:create --roles Administrator admin admin Admin User </info>
            5. Create your own site package or require an existing one
               - <info>./flow kickstart:site Vendor.Site</info>
               - <info>composer require neos/demo && ./flow flow:package:rescan</info>
            6. Import a site or create an empty one
               - <info>./flow site:import Vendor.Site</info>
               - <info>./flow site:create sitename Vendor.Site Vendor.Site:Document.HomePage</info>


            EOT
        );
    }

}
