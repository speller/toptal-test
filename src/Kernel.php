<?php

namespace App;

use App\Db\DependencyInjection\DbExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Symfony application kernel
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @inheritDoc
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @inheritDoc
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    /**
     * @inheritDoc
     */
    public function getProjectDir(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * @inheritDoc
     */
    public function registerBundles()
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        $container->registerExtension(new DbExtension());
        parent::prepareContainer($container);
    }

    /**
     * @inheritDoc
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $confDir = $this->getProjectDir() . '/config';
        $container->addResource(new FileResource($confDir . '/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $revisionFile = $this->getProjectDir() . '/image-tag.txt';
        $container->setParameter(
            'build_revision',
            file_exists($revisionFile) ?
                trim(file_get_contents($revisionFile)) :
                'unknown'
        );

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');
    }

    /**
     * @inheritDoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }
}
