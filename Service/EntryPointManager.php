<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;
use Cravler\FayeAppBundle\Ext\SystemExtInterface;
use Cravler\FayeAppBundle\Package\Package;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class EntryPointManager
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PackageManager $pm,
        private readonly SecurityManager $sm,
        private readonly ExtensionsChain $extChain,
        private readonly string $entryPointPrefix = '',
        private readonly string $securityUrlSalt = '',
    ) {
    }

    public function getPackageManager(): PackageManager
    {
        return $this->pm;
    }

    public function getSecurityManager(): SecurityManager
    {
        return $this->sm;
    }

    public function createPackage(EntryPointInterface $entryPoint, string $channel, mixed $data = null): Package
    {
        return new Package([
            'channel' => $this->prepareChannel($entryPoint, $channel),
            'data' => $data,
            'ext' => $this->getExt(),
        ]);
    }

    private function prepareChannel(EntryPointInterface $entryPoint, string $channel): string
    {
        return '/'.\str_replace(
            '.',
            '~',
            $this->entryPointPrefix.'@'.$entryPoint->getId(),
        ).$channel;
    }

    /**
     * @return array<string, mixed>
     */
    private function getExt(): array
    {
        $ext = [];
        foreach ($this->extChain->getExtensions() as $extension) {
            if ($extension instanceof SystemExtInterface) {
                $ext = \array_replace_recursive($ext, $extension->getSystemExt());
            }
        }

        $security = [
            'system' => $this->getSecurityManager()->createSystemToken(),
            'url' => $this->urlGenerator->generate('faye_app_security', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $security['url.hash'] = \md5($security['system'].';'.$security['url'].';'.$this->securityUrlSalt);

        $ext = \array_replace_recursive($ext, [
            'security' => $security,
        ]);

        return $ext;
    }
}
