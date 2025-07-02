# Prototype Integration

This TYPO3 extension makes it easy to use different template engines in TYPO3. It supports engines like Twig, Handlebars, Phug, and more, without needing to convert the markup to Fluid first. This is useful if you already have a pattern library in tools like Pattern Lab or Fractal.

Additionally, Prototype Integration can provide a JSON content API for your TYPO3 site.

## Installation

To use a specific template engine like twig, require the relevant pti flavor.

```bash
composer require pti/pti-twig
```

If you would just like to offer a content API and output json it is sufficient to install pti itself

```bash
composer require pti/pti
```


## Single content element

To render a single content element type via PTI, use the `PTI` content object:
```typo3_typoscript
tt_content.text = PTI
```

This way the tt_content row will be passed to the template engine.

Optionally, define a template, data processor and data processor configuration:
```typo3_typoscript
tt_content.textpic = PTI
tt_content.textpic {
    templateName = @content-elements/ce03-textpic/ce03-textpic.twig
    dataProcessors {
        10 = PrototypeIntegration\Demo\Processors\ContentElements\Ce03TextImage
        10 {
            picture {
                default {
                    width = 320c
                    height = 193c
                    cropVariant = default
                }

                variants {
                    5 {
                        config {
                            mediaQuery = (min-width: 1200px)
                            cropVariant = default
                            width = 945c
                            height = 570c
                        }
                    }
                }
            }
        }
    }
}
```

A sample data processor for a simple content element:
```php
<?php

namespace PrototypeIntegration\Demo\Processors\ContentElements;

use PrototypeIntegration\PrototypeIntegration\Processor\PictureProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\RichtextProcessor;

class Ce03TextImage implements PtiDataProcessor
{
    public function __construct(
        protected RichtextProcessor $richtextProcessor,
        protected PictureProcessor $pictureProcessor,
    ) {
    }

    public function process(array $data, array $configuration): ?array
    {
        $result = [
            'type' => 'ce03-textpic',
            'headline' => $data['header'],
            'richtext' => $this->richtextProcessor->processRteText($data['bodytext']),
        ];

        $pictures = $this->pictureProcessor->renderPicturesForRelation(
            'tt_content',
            'image',
            $data,
            $configuration['picture'],
        );

        if (isset($pictures[0])) {
            $result['picture'] = $pictures[0];
        }

        return $result;
    }
}
```

## Entire page via PTI
You can render an entire page via PTI, by using the pre-defined `CompoundProcessor`:

```typo3_typoscript
page = PAGE
page.10 = PTI
page.10 {
    templateName = @pages/default/default.twig
    dataProcessors {
        10 = PrototypeIntegration\PrototypeIntegration\Processor\CompoundProcessor
        10 {
            title = TEXT
            title.field = title

            content {
                main = PTI_CONTENT
                main {
                    table = tt_content
                    select {
                        orderBy = sorting
                        where = colPos = 0
                    }
                }
            }

            navigation {
                main = PTI
                main {
                    dataProcessors {
                        10 = PrototypeIntegration\PrototypeIntegration\Processor\PageElement\MenuProcessor
                        10 {
                            includePageData = 0
                            menuConfiguration {
                                entryLevel = 0
                                levels = 5
                                expandAll = 1
                            }
                        }
                    }
                }
            }
        }
    }
}
```


## Extbase integration

To integrate extbase actions with pti, override the TYPO3 `ViewFactory` with the pti implementation. This allows pti to inject custom views into extbase controllers. Add the following code to your `EXT:sitepackage/Configuration/Services.yaml`:

```yaml
# EXT:pti_demo/Configuration/Services.yaml
services:
  TYPO3\CMS\Core\View\ViewFactoryInterface:
    class: 'PrototypeIntegration\PrototypeIntegration\View\ViewFactory'
    arguments:
      $defaultViewFactory: '@TYPO3\CMS\Fluid\View\FluidViewFactory'
```

To render the output of an extbase action with a custom processor and template, create a `PtiDataProcessor` and add the `AsExtbaseProcessor` attribute to the class. You can optionally define a template. PTI will inject an adapter as extbase view to run the processors and pass the rendering to the template engine with the given template.

Here is an example for a News List integration:
```php
<?php

namespace PrototypeIntegration\Demo\Processors\Plugins\News;

use GeorgRinger\News\Controller\NewsController;
use GeorgRinger\News\Domain\Model\News;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsExtbaseProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\RichtextProcessor;

#[AsExtbaseProcessor(
    controller: NewsController::class,
    action: 'list',
    template: '@content-elements/ce02-news-list/ce02-news-list.twig',
)]
class NewsListView implements PtiDataProcessor
{
    public function __construct(
        protected RichtextProcessor $richtextProcessor,
    ) {
    }

    public function process(array $data, array $configuration): ?array
    {
        $newsItems = $data['news']->toArray();
        $result= [
            'type' => 'ce02-news-list',
            'headline' => $data['contentObjectData']['header'],
            'news' => array_map([$this, 'translateNewsItem'], $newsItems),
        ];
        return $result;
    }

    protected function translateNewsItem(News $newsItem): array
    {
        return [
            'title' => $newsItem->getTitle(),
            'teaser' => $newsItem->getTeaser(),
            'text' => $this->richtextProcessor->processRteText($newsItem->getBodytext()),
        ];
    }
}
```

If you need a custom adapter, you can define a custom `adapterClassName` in the `#AsExtbaseProcessor` attribute.
PTI ships a `FluidViewAdapter` that allows to fake Fluid views.

```php
<?php

namespace PrototypeIntegration\Demo\Processors\Plugins\Solr;

use ApacheSolrForTypo3\Solr\Controller\SearchController;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\SearchResultSet;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsExtbaseProcessor;
use PrototypeIntegration\PrototypeIntegration\Processor\PtiDataProcessor;
use PrototypeIntegration\PrototypeIntegration\View\FluidViewAdapter;

#[AsExtbaseProcessor(
    controller: SearchController::class,
    action: 'results',
    template: '@content-elements/ce04-search/ce04-search.twig',
    adapterClassName: FluidViewAdapter::class,
)]
class Results implements PtiDataProcessor
{
    public function process(array $data, array $configuration): ?array
    {
        …
    }
}
```
