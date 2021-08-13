<?php
namespace Vinou\VinouConnector\Middleware;

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as Debug;
use \TYPO3\CMS\Extbase\Object\ObjectManager;
use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use \TYPO3\CMS\Core\Site\Entity\Site;
use \TYPO3\CMS\Core\TypoScript\TemplateService;
use \TYPO3\CMS\Core\Utility\RootlineUtility;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Vinou\ApiConnector\Session\Session;
use \Vinou\ApiConnector\FileHandler\Pdf;
use \Vinou\VinouConnector\Utility\Helper;
use \Vinou\VinouConnector\Utility\Render;
use \Vinou\VinouConnector\Utility\Shop;
use \Vinou\VinouConnector\Utility\TypoScriptHelper;

/**
 * This class could called with AJAX via eID
 */
class AjaxActions implements MiddlewareInterface {

    protected $data = [];
    protected $settings = [];
    protected $errors = [];
    protected $api = null;

     /**
     * Check if the user must change the password and redirect to configured PID
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return RedirectResponse|ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!isset($request->getQueryParams()['vinou-command'])) {
            return $response;
        }

        $this->initialize($request);

        switch ($request->getQueryParams()['vinou-command']) {
            case 'cache-expertise':
                $this->cacheExpertise($request->getQueryParams()['wineID']);
                break;

            default:
                $this->data = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
                $this->regularAction();
                break;
        }

    }

    private function initialize($request) {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $this->settings = TypoScriptHelper::extractSettings('tx_vinouconnector_shop', $request);
        $this->api = Helper::initApi();
    }

    public function regularAction() {

        if (empty($this->data) || !isset($this->data['action']))
            $this->sendResult(false, 'no action defined');

        $action = $this->data['action'];
        unset($this->data['action']);
        switch ($action) {
            case 'init':
                $this->sendResult($this->api->initBasket($this->data), 'basket could not be initialized');
                break;

            case 'get':
                $result = $this->api->getBasket();
                if (!$result)
                    $this->sendResult(false, 'basket not found');
                else {
                    $result['quantity'] = Shop::calcCardQuantity($result['basketItems']);
                    $result['valid'] = Shop::quantityIsAllowed($result['quantity'], $this->settings['basket'], true);
                }

                $this->sendResult($result);
                break;

            case 'addItem':
                $this->sendResult($this->api->addItemToBasket($this->data), 'item could not be added');
                break;

            case 'editItem':
                $this->sendResult($this->api->editItemInBasket($this->data), 'item could not be updated');
                break;

            case 'deleteItem':
                $this->sendResult($this->api->deleteItemFromBasket($this->data['id']), 'item could not be deleted');
                break;

            case 'findPackage':
                $this->sendResult($this->api->getBasketPackage());
                break;

            case 'findCampaign':
                $result = $this->api->findCampaign($this->data);
                $campaign = Session::getValue('campaign');
                if ($this->result && $campaign && $this->result['uuid'] == $campaign['uuid'])
                    $this->sendResult(false, 'campaign already activated');
                else
                    $this->sendResult($result, 'campaign could not be resolved');
                break;

            case 'loadCampaign':
                $result = $this->api->findCampaign($this->data);
                if ($result) {
                    Session::setValue('campaign', $result);
                    $this->sendResult($result);
                }
                else
                    $this->sendResult(false, 'campaign could not be resolved');
                break;

            case 'removeCampaign':
                $this->sendResult(Session::deleteValue('campaign'), 'campaign could not be deleted');
                break;

            case 'campaignDiscount':
                $processor = new Shop($this->api);
                $this->sendResult($processor->campaignDiscount(), 'discount could not be fetched');
                break;

            default:
                $this->sendResult(false, 'action could not be resolved');
                break;
        }

    }

    private function cacheExpertise($wineID) {

        if ($wineID); {
            $wine = $this->api->getWine(GeneralUtility::_GET('wineID'));

            if (Helper::getExtConfValue('cacheExpertise') == 1) {
                $cachePDFProcess = Pdf::storeApiPDF(
                    $wine['expertisePdf'],
                    $wine['chstamp'],
                    Helper::getPdfCacheDir(),
                    $wine['id'].'-',
                    true
                );
                $redirectURL = '/' . Helper::getPdfCacheDir(false) . $cachePDFProcess['fileName'];
            }

            else
                $redirectURL = 'https://api.vinou.de' . $wine['expertisePdf'];

            header('Location: '.$redirectURL);
        }

        exit;
    }

    private function sendResult($result, $errorMessage = null) {

        if (!$result) {
            if (is_null($errorMessage))
                $result = ['no result created'];
            else
                array_push($this->errors, $errorMessage);
        }

        if (count($this->errors) > 0)
            Render::sendJSON([
                'info' => 'error',
                'errors' => $this->errors,
                'request' => $this->data
            ], 'error');
        else if ($result['info'] == 'error')
            Render::sendJSON(array_merge($result, ['request' => $this->data]), 'error');
        else
            Render::sendJSON($result);

        exit();
    }
}
