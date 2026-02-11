<?php

declare(strict_types=1);

namespace App\Delivery\Http\Controller\Admin;

use App\Application\Site\Handler\AddSiteSectionHandler;
use App\Application\Site\Handler\AddSiteSectionItemHandler;
use App\Application\Site\Handler\DeleteSiteSectionHandler;
use App\Application\Site\Handler\DeleteSiteSectionItemHandler;
use App\Application\Site\Handler\ListSiteSectionItemsHandler;
use App\Application\Site\Handler\ListSiteSectionsHandler;
use App\Application\Site\Handler\ReorderSiteSectionsHandler;
use App\Application\Site\Handler\UpdateSiteSectionHandler;
use App\Application\Site\Handler\UpdateSiteSectionItemHandler;

final class SiteSectionActionHandlers
{
    public function __construct(
        public readonly ListSiteSectionsHandler $listSectionsHandler,
        public readonly AddSiteSectionHandler $addSectionHandler,
        public readonly ReorderSiteSectionsHandler $reorderSectionsHandler,
        public readonly UpdateSiteSectionHandler $updateSectionHandler,
        public readonly DeleteSiteSectionHandler $deleteSectionHandler,
        public readonly ListSiteSectionItemsHandler $listSectionItemsHandler,
        public readonly AddSiteSectionItemHandler $addSectionItemHandler,
        public readonly UpdateSiteSectionItemHandler $updateSectionItemHandler,
        public readonly DeleteSiteSectionItemHandler $deleteSectionItemHandler,
    ) {}
}
