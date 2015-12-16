<?hh // strict

use HHVM\UserDocumentation\APIIndex;
use HHVM\UserDocumentation\APINavData;
use HHVM\UserDocumentation\APIDefinitionType;

enum APIProduct: string as string {
  HACK = 'hack';
}

final class APIListController extends WebPageController {
  public async function getTitle(): Awaitable<string> {
    switch ($this->getProduct()) {
      case APIProduct::HACK:
        return 'Hack APIs';
    }
  }

  protected function getDefinitions(
  ): Map<APIDefinitionType, Map<string, string>> {
    $out = Map {};
    foreach (APIDefinitionType::getValues() as $type) {
       $index = APIIndex::getIndexForType($type);
       $out[$type] = Map { };
       foreach ($index as $node) {
         $out[$type][$node['name']] = $node['urlPath'];
       }
    }
    return $out;
  }

  protected function getInnerContent(): XHPRoot {
    $defs = $this->getDefinitions();
    $type = $this->getOptionalStringParam('type');
    if ($type !== null) {
      $type = APIDefinitionType::assert($type);
      $defs = Map { $type => $defs[$type] };
    }

    $root = <div class="referenceList" />;
    foreach ($defs as $type => $api_references) {
      $title = ucwords($type.' Reference');
      $type_list = <ul class="apiList" />;
      foreach ($api_references as $name => $url) {
        $type_list->appendChild(
          <li>
            <a href={$url}>{$name}</a>
          </li>
        );
      }

      $root->appendChild(
        <div class="referenceType">
          <h3 class="listTitle">{$title}</h3>
          {$type_list}
        </div>
      );
    }
    return $root;
  }

  protected async function getBody(): Awaitable<XHPRoot> {
    return
      <div class="apiListWrapper">
        {$this->getInnerContent()}
      </div>;
  }

  protected function getBreadcrumbs(): XHPRoot {
    $product = 'hack';
    $product_root_url = sprintf(
      "/%s/",
      $product,
    );
    $reference_root_url = sprintf(
      "/%s/reference/",
      $product,
    );

    $breadcrumbs =
      <x:frag>
        <span class="breadcrumbRoot">
          <a href="/">Documentation</a>
        </span>
        <i class="breadcrumbSeparator" />
        <span class="breadcrumbProductRoot">
          <a href={$product_root_url}>{$product}</a>
        </span>
      </x:frag>;

    $type = $this->getOptionalStringParam('type');
    if ($type !== null) {
      $breadcrumbs->appendChild(
        <x:frag>
          <i class="breadcrumbSeparator" />
          <span class="breadcrumbSecondaryRoot">
            <a href={$reference_root_url}>Reference</a>
          </span>
          <i class="breadcrumbSeparator" />
          <span class="breadcrumbTypeRoot breadcrumbCurrentPage">
            {$type}
          </span>
        </x:frag>
      );
    } else {
      $breadcrumbs->appendChild(
        <x:frag>
          <i class="breadcrumbSeparator" />
          <span class="breadcrumbSecondaryRoot breadcrumbCurrentPage">
            Reference
          </span>
        </x:frag>
      );
    }

    return
      <div class="breadcrumbNav">
        <div class="widthWrapper">
          {$breadcrumbs}
        </div>
      </div>;
  }

  <<__Memoize>>
  private function getProduct(): APIProduct {
    return APIProduct::assert(
      $this->getRequiredStringParam('product')
    );
  }
}
