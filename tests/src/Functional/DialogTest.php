<?php

namespace Drupal\Tests\editor_advanced_link\Functional;

use Drupal\Tests\BrowserTestBase;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\editor\Entity\Editor;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\ckeditor\Traits\CKEditorTestTrait;

/**
 * Test the module form editor link dialog alter
 *
 * @group editor_advanced_link
 */
class DialogTest extends BrowserTestBase {

  use CKEditorTestTrait;

  /**
   * The account.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $account;

  /**
   * The FilterFormat config entity used for testing.
   *
   * @var \Drupal\filter\FilterFormatInterface
   */
  protected $filterFormat;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'ckeditor', 'filter', 'ckeditor_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a text format and associate CKEditor.
    $this->filterFormat = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
    ]);
    $this->filterFormat->save();

    Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ])->save();

    // Create a node type for testing.
    NodeType::create(['type' => 'page', 'name' => 'page'])->save();

    $field_storage = FieldStorageConfig::loadByName('node', 'body');

    // Create a body field instance for the 'page' node type.
    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'page',
      'label' => 'Body',
      'settings' => ['display_summary' => TRUE],
      'required' => TRUE,
    ])->save();

    // Assign widget settings for the 'default' form mode.
    EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'page',
      'mode' => 'default',
      'status' => TRUE,
    ])->setComponent('body', ['type' => 'text_textarea_with_summary'])
      ->save();

    $this->account = $this->drupalCreateUser([
      'administer nodes',
      'create page content',
      'use text format filtered_html',
    ]);
    $this->drupalLogin($this->account);
  }

  /**
   * Tests the setting form.
   */
  public function testDialog() {

    $session = $this->getSession();
    $web_assert = $this->assertSession();

    $this->drupalGet('node/add/page');
    $session->getPage();

    // We can test all of the DOM elements and css attributes on the site
    // on this way:
    $web_assert->elementExists('css', '.cke_button__drupallink');

    // Asserts the image dialog opens when clicking the Image button.
    $this->click('.cke_button__drupallink');
    $this->assertNotEmpty($web_assert->waitForElement('css', '.ui-dialog'));
    $web_assert->elementContains('css', '.ui-dialog .ui-dialog-titlebar', 'Insert Image');
  }

}
