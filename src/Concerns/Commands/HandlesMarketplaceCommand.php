<?php

namespace MyListerHub\Core\Concerns\Commands;

use MyListerHub\Core\Enums\Marketplace;

trait HandlesMarketplaceCommand
{
    public function getMarketplace(?string $marketplace): string
    {
        while (! $marketplace || ! in_array($marketplace, Marketplace::getValues(), true)) {
            $marketplace = (string) $this->choice('Select a marketplace', Marketplace::getValues(), Marketplace::eBay);
        }

        return $marketplace;
    }

    public function getMarketplaceArgument(string $argument = 'marketplace'): string
    {
        return $this->getMarketplace($this->argument($argument));
    }

    public function getMarketplaceOption(string $option = 'marketplace'): string
    {
        return $this->getMarketplace($this->option($option));
    }
}
