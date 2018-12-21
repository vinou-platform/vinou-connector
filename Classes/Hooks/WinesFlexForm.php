<?php

namespace Vinou\VinouConnector\Hooks;

class WinesFlexForm {

  protected $objectManager = NULL;
  protected $persistenceManager = NULL;
  protected $extKey = 'vinou';
  protected $extConf = NULL;
  protected $api = NULL;
  protected $llPath = 'Resources/Private/Language/';

  public function init() {
    $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    $this->persistenceManager = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager');
    $configurationUtility = $this->objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
    $this->extConf = $configurationUtility->getCurrentConfiguration($this->extKey);
    $this->llPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($this->extKey).$this->llPath;

    $dev = false;
    if ($this->extConf['vinouMode']['value'] == 'dev') {
      $dev = true;
    }

    $this->api = new \Vinou\VinouConnector\Utility\Api(
      $this->extConf['token']['value'],
      $this->extConf['authId']['value'],
      $dev
    );
  }

  /**
   * user template layout
   * @param array $config
   */
  public function user_templateLayout(array &$config) {
    $templateUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Vinou\\VinouConnector\\Utility\\IfTemplateLayoutUtility');
    $templateLayouts = $templateUtility->getAvailableTemplateLayouts($config['row']['pid']);
    foreach ($templateLayouts as $layout) {
      $additionalLayout = array(
        $GLOBALS['LANG']->sL($layout[0], TRUE),
        $layout[1]
      );
      array_push($config['items'], $additionalLayout);
    }
  }

  /**
   * fetch vinou Categories
   * @param array $config
   */
  public function vinouCategories(array &$config) {
    $this->init();
    $categories = $this->api->getCategoriesAll();
    foreach ($categories as $category) {
      array_push($config['items'], array($category['name'],$category['id']));
    }
  }

  /**
   * fetch vinou Types
   * @param array $config
   */
  public function vinouTypes(array &$config) {
    $this->init();
    $winetypes = json_decode(file_get_contents($this->llPath.'winetypes.json'),true);
    isset($winetypes[$GLOBALS['BE_USER']->uc['lang']]) ? $winetypes = $winetypes[$GLOBALS['BE_USER']->uc['lang']] : $winetypes = $winetypes['en'];
    foreach ($winetypes as $key => $label) {
      array_push($config['items'], array($label,$key));
    }
  }

}
