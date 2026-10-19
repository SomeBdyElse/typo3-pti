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

To render a single content element, use the `PTI` content object. Without a configured or registered processor, the `tt_content` row is passed unchanged to the view.

```typo3_typoscript
tt_content.textpic = PTI
```

If your view is a template engine and needs a template path, define it via `templateName`.

```typo3_typoscript
tt_content.text = PTI
tt_content.text {
    templateName = @content-elements/ce03-textpic/ce03-textpic.twig
}
```

### Data processing

There are two ways to process the data before passing it to the template: a single record processor or multiple data processors.

If `dataProcessors` is set, it takes precedence, even when the chain is empty. Otherwise, PTI uses an explicit `processor`, then checks the record processor registry. If no processor is found, it passes the original row to the template.

#### Record processor

Create a record processor and mark it with the `AsRecordProcessor` attribute. The attribute identifies the main type (the database table) and the record type, such as `textpic` from the `CType` field of `tt_content`.

```php
<?php

namespace Vendor\Site\Processor;

use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsRecordProcessor;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\RecordInterface;

#[AsRecordProcessor(mainType: 'tt_content', recordType: 'text')]
final class TextPicProcessor
{
    public function __invoke(
        ServerRequestInterface $request,
        array $configuration,
        RecordInterface $record,
    ): array {
        return [
            'headline' => (string)$record->get('header'),
            'text' => (string)$record->get('bodytext'),
        ];
    }
}
```

The processor receives the request, the TypoScript configuration as a plain array, and a resolved `tt_content` `Record`. Its result is passed to the template. The current content object renderer is available through `$request->getAttribute('currentContentObject')`.

Instead of registering the processor via an attribute, you can select it explicitly in TypoScript:

```typo3_typoscript
tt_content.text = PTI
tt_content.text.processor = Vendor\Site\Processor\TextProcessor
```

Record processors can return objects as well as arrays. For example, define a view model:

```php
<?php

namespace Vendor\Site\View\Model;

readonly class TextViewModel
{
    public function __construct(
        public string $headline,
        public string $text,
    ) {
    }
}
```

To return the view model, replace the array-returning processor above with:

```php
<?php

namespace Vendor\Site\Processor;

use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsRecordProcessor;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Domain\RecordInterface;
use Vendor\Site\View\Model\TextPicViewModel;

#[AsRecordProcessor(mainType: 'tt_content', recordType: 'text')]
final class TextProcessor implements PtiRecordProcessor
{
    public function __invoke(
        ServerRequestInterface $request,
        array $configuration,
        RecordInterface $record,
    ): ?TextPicViewModel {
        return new TextViewModel(
            headline: (string)$record->get('header'),
            text: (string)$record->get('bodytext'),
        );
    }
}
```

Returning `null` from the processor produces an empty string without rendering a template.

Although you can return objects from record processors, PTI views expect an array of template variables. If the processor returns an array, it is passed directly to the view.
If the processor returns an object, you can normalize it into top-level template variables or pass it unchanged under a named variable.

To normalize an object:

```typo3_typoscript
tt_content.text.normalize = 1
```

The template can then access the variables directly, for example with `{{ headline }}`.

To pass an object unchanged under a named variable:

```typo3_typoscript
tt_content.text.normalize = 0
tt_content.text.as = model
```

The template can then access the object through that variable, for example with `{{ model.headline }}`. By default, normalization is disabled and object results are available as `data`.

#### Data processors

Instead of defining a single record processor, you can use a chain of data processors implementing `PtiDataProcessor`.

The first data processor receives the database row as an array. Each subsequent processor receives the previous processor's result. The final array supplies the template variables; returning `null` stops processing and skips rendering.

Here is a sample data processor for a simple content element:

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
