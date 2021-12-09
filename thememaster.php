<?php
/**
 * Copyright (C) 2017-2019 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017-2019 thirty bees
 * @license   Academic Free License (AFL 3.0)
 */

/**
 * Class thememaster
 *
 * @property $bootstrap
 */
class thememaster extends Module
{
	private $_html = '';
	
    public static $tabs = array(
        array('id'  => '0', 'name' => 'general'),
        array('id'  => '1', 'name' => 'header'),
        array('id'  => '2', 'name' => 'footer'),
        array('id'  => '3,4,5,6', 'name' => 'colors'),
		array('id'  => '7,8', 'name' => 'order'),
    );
	
    /**
     * thememaster constructor.
     */
    public function __construct()
    {
        $this->name = 'thememaster';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'thirty bees';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Theme Master Configurator');
        $this->description = $this->l('Configuration for theme blocks and content.');
        $this->tb_versions_compliancy = '> 1.0.0';
        $this->tb_min_version = '1.0.0';

        $this->bootstrap = true;
    }

    /**
     * Installs module to Thirtybees
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function install()
    {
        parent::install();

        $hooksToUnhook = [
			['module' => 'blocknewsletter', 'hook' => 'footer'],
            ['module' => 'blockcategories', 'hook' => 'footer'],
			['module' => 'blockcms', 'hook' => 'footer'],
			['module' => 'blockmyaccountfooter', 'hook' => 'footer'],
        ];
        foreach ($hooksToUnhook as $unhook) {
            $this->unhookModule($unhook['module'], $unhook['hook']);
        }

        $hooksToInstall = ['displayHeader', 'displayHome', 'displayBanner', 'displayNav'];
        foreach ($hooksToInstall as $hookName) {
            $this->registerHook($hookName);
        }

        // Translatable configuration items
        foreach (Language::getLanguages(false) as $language) {
            $idLanguage = (int) $language['id_lang'];
            Configuration::updateValue(
                'TM_HOME_CONTENT',
                [
                    $idLanguage => 'Welcome on our online shopping website',
                ]
            );
			Configuration::updateValue(
                'TM_SLOGAN_CONTENT',
                [
                    $idLanguage => 'Enjoy shopping in our online boutique',
                ]
            );
			Configuration::updateValue(
                'TM_COPYRIGHT_CONTENT',
                [
                    $idLanguage => '&copy; Acme Corporation 2021',
                ]
            );
        }	
		
		// Colors configuration default
		Configuration::updateValue('TM_BACKGROUND_BODY_COLOR', '#ffffff');
		Configuration::updateValue('TM_PRODLISTBLOCKBG_COLOR', '#ffffff');
		Configuration::updateValue('TM_BACKGROUND_SLOGAN_COLOR', '#ffffff');
		Configuration::updateValue('TM_SLOGAN_CONTENT_COLOR', '#333333');
		Configuration::updateValue('TM_UNDERMENU_CONTENT_COLOR', '#ffffff');
		Configuration::updateValue('TM_BACKGROUND_UNDERMENU_COLOR', '#000000');
		Configuration::updateValue('TM_UNDERMENU_OPACITY', '1');
		Configuration::updateValue('TM_BACKGROUND_NAVBAR_COLOR', '#f4f4f4');
		Configuration::updateValue('TM_BACKGROUND_HEADER_COLOR', '#ffffff');
		Configuration::updateValue('TM_BACKGROUND_CONTENT_COLOR', '#ffffff');	
		Configuration::updateValue('TM_BACKGROUND_FOOTER_COLOR', '#121212');
		Configuration::updateValue('TM_TEXT_FOOTER_COLOR', '#c8c8c8');
		Configuration::updateValue('TM_TEXT_FOOTER_TITLE_COLOR', '#ffffff');
		Configuration::updateValue('TM_LINK_FOOTER_COLOR', '#c8c8c8');
		Configuration::updateValue('TM_LINKHOVER_FOOTER_COLOR', '#ff6f61');
		
        return true;
    }

    /**
     * Unhooks a module hook
     *
     * @param string $module
     * @param string $hook
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function unhookModule($module, $hook)
    {
        $id_module = Module::getModuleIdByName($module);
        $id_hook = Hook::getIdByName($hook);

        return Db::getInstance()->delete('hook_module', 'id_module = '.(int) $id_module.' AND id_hook = '.(int) $id_hook);
    }

    /***
     * Uninstalls module from Thirtybees
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function uninstall()
    {
        $keysToDrop = [
			'TM_HOME_CONTENT',
			'TM_CONTAINERFULL',
			'TM_STICKY_MENU',
			'TM_SLOGAN_CONTENT',
			'TM_SLOGAN_ALIGN',
			'TM_BACKGROUND_SLOGAN_COLOR',
			'TM_SLOGAN_CONTENT_COLOR',
			'TM_SLOGAN_CONTENT_WEIGHT',
			'TM_UNDERMENU0_CONTENT',
			'TM_UNDERMENU1_CONTENT',
			'TM_UNDERMENU2_CONTENT',
			'TM_UNDERMENU_CONTENT_COLOR',
			'TM_BACKGROUND_UNDERMENU_COLOR',
			'TM_UNDERMENU_OPACITY',
			'TM_BACKGROUND_BODY_COLOR',
			'TM_PRODLISTBLOCKBG_COLOR',
			'TM_BACKGROUND_HEADER_COLOR',		
			'TM_BACKGROUND_CONTENT_COLOR',	
			'TM_BLOCKNEWSLETTER_FOOTER',
            'TM_BLOCKCATEGORIES_FOOTER',
			'TM_BLOCKCMS_FOOTER',
			'TM_BLOCKMYACCOUNT_FOOTER',
            'TM_COPYRIGHT_CONTENT',
			'TM_BACKGROUND_NAVBAR_COLOR',
			'TM_BACKGROUND_FOOTER_COLOR',
			'TM_TEXT_FOOTER_COLOR',
			'TM_TEXT_FOOTER_TITLE_COLOR',
			'TM_LINK_FOOTER_COLOR',
			'TM_LINKHOVER_FOOTER_COLOR',
			'TM_GFONT_ON',
			'TM_GFONT_BODY',
			'TM_QTTYINCART_ON' ,
			'TM_TITLEHEADING_OFF',
			'TM_ORDERHEADING_OFF',
			'TM_GENDERTITLE_OFF',
			'TM_BIRTHDAY_OFF',
        ];
        foreach ($keysToDrop as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    /**
     * Compiles and returns module configuration page content
     *
     * @return string
     * @throws Exception
     * @throws HTMLPurifier_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
		$this->context->controller->addCSS(($this->_path).'views/css/admin.css');
        $this->context->controller->addJS(($this->_path).'views/js/admin.js');
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->postProcess();
        }

        $moduleUrl = $this->context->link->getAdminLink('AdminModules').'&configure='.$this->name;

        $fieldSets = [
            '0' => [
                'title'   => $this->l('Theme settings'),
                'fields'  => $this->getOptionFieldsGeneral(),
				'name'    => 'general',
                'submit'  => [
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ],
            ],
			'1' => [
                'title'   => $this->l('Theme settings - Header'),
                'fields'  => $this->getOptionFieldsHeader(),
				'name' 	  => 'header',
                'submit'  => [
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ],
            ],
			'2' => [
                'title'   => $this->l('Theme settings - Footer'),
                'fields'  => $this->getOptionFieldsFooter(),
				'name'    => 'footer',
                'submit'  => [
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ],
            ],
			'3' => [
                'title'   => $this->l('Theme settings - Colors - General'),
                'fields'  => $this->getOptionFieldsColors(),
				'name'    => 'colors',
            ],
			'4' => [
                'title'   => $this->l('Theme settings - Colors - Header'),
                'fields'  => $this->getOptionFieldsHeaderColors(),
				'name'    => 'headercolors',
            ],
			'5' => [
                'title'   => $this->l('Theme settings - Colors - Content'),
                'fields'  => $this->getOptionFieldsContentColors(),
				'name'    => 'contentcolors',
            ],
			'6' => [
                'title'   => $this->l('Theme settings - Colors - Footer'),
                'fields'  => $this->getOptionFieldsFooterColors(),
				'name'    => 'footercolors',
                'submit'  => [
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ],
            ],
			'7' => [
                'title'   => $this->l('Theme settings - Order process'),
                'fields'  => $this->getOptionFieldsOrder(),
				'name' 	  => 'order',
            ],
			'8' => [
                'title'   => $this->l('Theme settings - MOBILE - Order'),
                'fields'  => $this->getOptionFieldsMobileOrder(),
				'name' 	  => 'mobileorder',
                'submit'  => [
                    'name'  => 'submit'.$this->name,
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $h = new HelperOptions();
        $h->token = Tools::getAdminTokenLite('AdminModules');
        $h->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $h->id = Tab::getIdFromClassName('AdminTools');

		return $this->_html.'<div class="thememaster row ">'.$this->leftTab().'<div id="thememaster" class="col-xs-12 col-lg-10 tab-content">'.$h->generateOptions($fieldSets).'</div></div>';
    }

    /**
     * Processes submitted configuration variables
     *
     * @throws PrestaShopException
     * @throws Adapter_Exception
     */
    protected function postProcess()
    {
        $castFunctions = ['boolval', 'doubleval', 'floatval', 'intval', 'strval'];
        $langIds = Language::getIDs(false);

        $values = [];
		$datas_array = [
			$this->getOptionFieldsGeneral(),
			$this->getOptionFieldsHeader(),
			$this->getOptionFieldsFooter(),
			$this->getOptionFieldsColors(),
			$this->getOptionFieldsHeaderColors(),
			$this->getOptionFieldsContentColors(),
			$this->getOptionFieldsFooterColors(),
			$this->getOptionFieldsOrder(),
			$this->getOptionFieldsMobileOrder(),
		];
	
		foreach ($datas_array as $data) {	
			foreach ($data as $key => $field) {	
				$htmlAllowed = isset($field['html']) && $field['html'];
				if ($field['type'] == 'textareaLang') {
					$values[$key] = [];
					foreach ($langIds as $idLang) {
						$value = Tools::getValue($key.'_'.$idLang);
						if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
							$value = call_user_func($field['cast'], $value);
						}
						$values[$key][$idLang] = $value;
					}
				}			
				else {
					$value = Tools::getValue($key);
					if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
						$value = call_user_func($field['cast'], $value);
					}
					$values[$key] = $value;	
				}
				Configuration::updateValue($key, $values[$key], $htmlAllowed);
			}
		}
			
		// Start to initialize and fill CSS file 
		$css = '';
				
		//check for Fonts Google to setup
		
		if ($values['TM_GFONT_ON'] && $values['TM_GFONT_BODY']) {
			$myfont = Configuration::get('TM_GFONT_BODY');
			$gfont = explode(",", $myfont, 2);
			$gfont = str_replace('\'','',$gfont[0]);
			$css .='@import url(\'https://fonts.googleapis.com/css?family='.$gfont.'&display=swap\'); body { font-family: '.$myfont.'; }
			';
		}
		if ($values['TM_CONTAINERFULL']) {
			$css .='.container {max-width:100%;} nav.breadcrumbs.text-center {text-align: left !important;} body#product #columns.container {max-width: 1480px;}
			';
		} 			
		$backgroundbodycolor = Configuration::get('TM_BACKGROUND_BODY_COLOR');
		$backgroundnavbarcolor = Configuration::get('TM_BACKGROUND_NAVBAR_COLOR');	
		$backgroundheadercolor = Configuration::get('TM_BACKGROUND_HEADER_COLOR');
		$backgroundcontentcolor = Configuration::get('TM_BACKGROUND_CONTENT_COLOR');		
		$backgroundfootercolor = Configuration::get('TM_BACKGROUND_FOOTER_COLOR');	
		$textfootercolor = Configuration::get('TM_TEXT_FOOTER_COLOR');
		$footertitlecolor =	Configuration::get('TM_TEXT_FOOTER_TITLE_COLOR');
		$footerlinkcolor = Configuration::get('TM_LINK_FOOTER_COLOR');
		$footerlinkhovercolor =	Configuration::get('TM_LINKHOVER_FOOTER_COLOR');	
		$prodlistblockbgcolor =	Configuration::get('TM_PRODLISTBLOCKBG_COLOR');	
	
	
		/* Write a CSS file to modify colors */
		$css  .= 'body { background-color:'.$backgroundbodycolor.'; } 
.navbar.navbar-default { background-color:'.$backgroundnavbarcolor.'; } 
#header, #header-blocks { background-color:'.$backgroundheadercolor.'; }
.top_column_wrapper, #columns, main { background-color:'.$backgroundcontentcolor.'; }
#footer { background-color:'.$backgroundfootercolor.'; }
#footer, #copyright-footer { color:'.$textfootercolor.'; }
#footer .footer-title, #footer .title_block { color:'.$footertitlecolor.'; }
#footer a { color:'.$footerlinkcolor.'; }
#footer a:hover { color:'.$footerlinkhovercolor.'; }
.ajax_block_product	{ background-color:'.$prodlistblockbgcolor.'; }
';

		/* Adding more Css code for Slogan */
		$sloganbackgroundcolor = Configuration::get('TM_BACKGROUND_SLOGAN_COLOR');
		$slogancontentcolor = Configuration::get('TM_SLOGAN_CONTENT_COLOR');	
		$css .= '#slogan { background: '.$sloganbackgroundcolor.'} 
#slogan p { color: '.$slogancontentcolor.'; font-size: 13px; padding: 1px 15px 3px; margin-bottom: 0;';
		$sloganalign = Configuration::get('TM_SLOGAN_ALIGN');	
		if ($sloganalign == '0') $css .= 'text-align: left;';
		if ($sloganalign == '1') $css .= 'text-align: center;';
		elseif ($sloganalign == '2') $css .= 'text-align: right;';
		$slogancontentweight = Configuration::get('TM_SLOGAN_CONTENT_WEIGHT');
		if ($slogancontentweight == '0') $css .= 'font-weight: 100;';	
		elseif ($slogancontentweight == '1') $css .= 'font-weight: 400;';
		elseif ($slogancontentweight == '2') $css .= 'font-weight: 700;';
		elseif ($slogancontentweight == '3') $css .= 'font-weight: 900;';
		$css .= '}
';

		/* Adding more Css code for Slider text under menu section */
		$undermenubackgroundcolor = Configuration::get('TM_BACKGROUND_UNDERMENU_COLOR');
		$undermenucontentcolor = Configuration::get('TM_UNDERMENU_CONTENT_COLOR');	
		$undermenuopacity = Configuration::get('TM_UNDERMENU_OPACITY');
		$css .= '#undermenu {background: '.$undermenubackgroundcolor.'; height:30px;';
		if ($undermenuopacity < 1) $css .= 'opacity: '.$undermenuopacity.';';
		$css .= 'width:100%;height:30px;margin-left:auto;margin-right:auto; border:0;padding:1px;z-index:100;overflow:hidden;white-space:nowrap;box-sizing:border-box;}';
		if ($undermenuopacity < 1) $css .= '@media (min-width:992px){#index #undermenu {margin-bottom:-30px;}}';
		$css .= '#undermenu p {color: '.$undermenucontentcolor.';margin: 0;text-transform: uppercase;font-size:15px;';
		$undermenucontentweight = Configuration::get('TM_UNDERMENU_CONTENT_WEIGHT');
		if ($undermenucontentweight == '0') $css .= 'font-weight: 300;';	
		elseif ($undermenucontentweight == '1') $css .= 'font-weight: 400;';
		elseif ($undermenucontentweight == '2') $css .= 'font-weight: 600;';
		elseif ($undermenucontentweight == '3') $css .= 'font-weight: 700;';
		$css .= '}
';
	
		/* Sliding text animation */
		$css .= '#undermenu .hmove {display: flex; position: relative; top: 0; right: 0;}
#undermenu .hslide {margin: 0; padding: 2px; width: 100%; flex-shrink: 0; box-sizing: border-box;}
@keyframes mySlides {0% {right:0} 30% {right:0} 33% {right:100%} 63% {right:100%} 66% {right:200%} 97% {right:200%} 100% {right:0}}
.hmove {animation: mySlides linear 14s infinite;}
.hmove:hover {animation-play-state: paused;}
';
		
		/* Adding more css for Home text */
		$css .= '#hometext {text-align: center; padding: 15px; margin: 30px 0 20px; font-size: 150%;}
';
		
		/* Adding more css for Header Sticky Menu on Scroll */
		/* Add some top padding to the page content to prevent sudden quick movement (as the header gets a new position at the top of the page (position:fixed and top:0) */
		/* The sticky class is added to the header with JS when it reaches its scroll position */
		if ($values['TM_STICKY_MENU']) {
			$css .= '.sticky {position: fixed;top: 0;width: 100%;z-index: 9998;background: #fff;} 
.sticky::before {background: #fff;position: fixed;top: 0;left: 0;width: 100%;height: 84px;} 
.sticky + .content {padding-top: 130px;}
';}
		/* enf of sticky menu */
		
		/* Heading Titles hidden if selected */
		if ($values['TM_TITLEHEADING_OFF']) { $css .= 'h1.page-heading {display: none;}
'; }							
		if ($values['TM_ORDERHEADING_OFF']) { $css .= '@media (max-width:767.98px){	#order h1.page-heading {display: none;}}
'; }
		
		/* Gender title hidden on account creation page */
		if ($values['TM_GENDERTITLE_OFF']) { $css .= '.title-form {display: none;}
'; }
		
		/* Birthday form is hidden on account creation page */
		if ($values['TM_BIRTHDAY_OFF']) { $css .= '#account-creation_form .date-select, #identity form .date-select { display: none; }
'; }								

		@chmod( '../modules/thememaster/views/css/colors-'.$this->context->shop->id.'.css',0777);
        $xml = fopen('../modules/thememaster/views/css/colors-'.$this->context->shop->id.'.css','w');
        fwrite($xml,$css);
		/* end of CSS file script */
		
		/* Start of JS file script */
		$js = '';
		
/* Header Sticky Menu on Scroll */
if ($values['TM_STICKY_MENU']) {
$js = '
// When the user scrolls the page, execute StickyMenu
$( document ).ready(function() {
	// Get the header
	var header = document.getElementById("headertopmenu");
	
	if (header !== null) {
		// Get the offset position of the navbar
		var sticky = header.offsetTop;
		
		// When the user scrolls the page, execute StickyMenu
		window.onscroll = function() { StickyMenu(); };

		// Add the sticky class to the header when you reach its scroll position. Remove "sticky" when you leave the scroll position
		function StickyMenu() {
		  if (window.pageYOffset > sticky) {
			header.classList.add("sticky");
		  } else {
			header.classList.remove("sticky");
		  }
		}
	}	
});
 ';}
/* enf of sticky menu */
		
		@chmod( '../modules/thememaster/views/js/tmcustom-'.$this->context->shop->id.'.js',0777);
        $xml = fopen('../modules/thememaster/views/js/tmcustom-'.$this->context->shop->id.'.js','w');
        fwrite($xml,$js);
		/* end of JS file script */
		
		if ($values['TM_BLOCKNEWSLETTER_FOOTER']) {
            $this->hookModule('blocknewsletter', 'footer');
        } else {
            $this->unhookModule('blocknewsletter', 'footer');
        }
		
		if ($values['TM_BLOCKCATEGORIES_FOOTER']) {
            $this->hookModule('blockcategories', 'footer');
        } else {
            $this->unhookModule('blockcategories', 'footer');
        }
		
		if ($values['TM_BLOCKCMS_FOOTER']) {
            $this->hookModule('blockcms', 'footer');
        } else {
            $this->unhookModule('blockcms', 'footer');
        }
		
		if ($values['TM_BLOCKMYACCOUNT_FOOTER']) {
            $this->hookModule('blockmyaccountfooter', 'footer');
        } else {
            $this->unhookModule('blockmyaccountfooter', 'footer');
        }
		
		/* Clear Smarty Cache */
		Tools::clearSmartyCache();
    }
	
	protected function getGooglefonts() {
		$json = file_get_contents('../modules/thememaster/fonts.json');
        $fonts_decode = json_decode( $json, TRUE );
        foreach ( $fonts_decode['items'] as $key => $value ) {
            $this->list_fonts[$key]['name'] = $value['family'];
			$this->list_fonts[$key]['value'] = '\''.str_replace(' ','+',$value['family']).'\', '.$value['category'];
        }
		return($this->list_fonts); 
	}
	
	protected function getOpacity() {
		$opacity = [];
        for ($i = 1; $i < 10; $i++) {
			$opacity[$i]['name'] = $i;
			$opacity[$i]['value'] = '0.'.$i.'';
		}
		$opacity[0]['name'] = '10';
		$opacity[0]['value'] = '1';
		return($opacity); 
	}
	
    /**
     * Return HelperOptions fields that are used in module configuration form.
     *
     * @return array
     */
	protected function getOptionFieldsGeneral()
    {
        return [
            'TM_HOME_CONTENT' => [
                'title' => $this->l('Home page content'),
                'desc'  => $this->l('Text to be displayed in the home page.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_CONTAINERFULL' => [
                'title' => $this->l('Container full width mode'),
                'desc'  => $this->l('Full width or boxed version.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_STICKY_MENU' => [
                'title' => $this->l('Sticky navigation bar'),
                'desc'  => $this->l('Make the header menu fixed on scroll.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_GFONT_ON' => [
                'title' => $this->l('Use Google Font'),
                'desc'  => $this->l('Load your favorite Google font for all pages.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_GFONT_BODY' => [
                'title' => $this->l('Choose your Google Font'),
                'desc'  => $this->l('Select your font for your site in the most populars Google fonts.'),
				'name' => 'TM_GFONT_BODY',
				'cast' => 'strval',
				'type' => 'select',
				'list' => $this->getGooglefonts(),	
				'identifier' => 'value',
            ],
			'TM_TITLEHEADING_OFF' => [
                'title' => $this->l('Hide H1 Heading titles on all pages'),
				'desc'  => $this->l('On all the website, hide titles to give more space to content.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ]
		];
    } 
	
	protected function getOptionFieldsHeader()
    {
        return [
            'TM_SLOGAN_CONTENT' => [
                'title' => $this->l('Header text in top of page'),
                'desc'  => $this->l('Text to be displayed in the top of pages as a slogan.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_SLOGAN_CONTENT_WEIGHT' => [
                'title' => $this->l('Header text font weight'),
				'hint'  => $this->l('Some weights might not be available with your selected fonts.'),
				'name' => 'TM_SLOGAN_CONTENT_WEIGHT',
				'type' => 'radio',
				'choices' => [
					$this->l('light'),
					$this->l('normal'),
					$this->l('bold'),
					$this->l('bolder')
				],
                'validation' => 'isUnsignedInt',
			],
			'TM_SLOGAN_ALIGN' => [
                'title' => $this->l('Header text alignment in top of page'),
                'desc'  => $this->l('Alignment of the slogan.'),
				'hint'  => $this->l('Select the best alignment for slogan in top of pages.'),
				'name' => 'TM_SLOGAN_ALIGN',
				'type' => 'radio',
				'choices' => [
					$this->l('left align'),
					$this->l('centered'),
					$this->l('right align')
				],
                'validation' => 'isUnsignedInt',
			],
			'TM_UNDERMENU0_CONTENT' => [
                'title' => $this->l('Text slider under menu 0'),
                'desc'  => $this->l('First text to be displayed under main menu.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_UNDERMENU1_CONTENT' => [
                'title' => $this->l('Text slider under menu 1'),
                'desc'  => $this->l('Second text to be displayed under main menu.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_UNDERMENU2_CONTENT' => [
                'title' => $this->l('Text slider under menu 2'),
                'desc'  => $this->l('Third text to be displayed under main menu.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_UNDERMENU_CONTENT_WEIGHT' => [
                'title' => $this->l('Text slider under menu font weight'),
				'hint'  => $this->l('Some weights might not be available with your selected fonts.'),
				'name' => 'TM_UNDERMENU_CONTENT_WEIGHT',
				'type' => 'radio',
				'choices' => [
					$this->l('light'),
					$this->l('normal'),
					$this->l('bold'),
					$this->l('bolder')
				],
                'validation' => 'isUnsignedInt',
			],
		];
    } 
	protected function getOptionFieldsFooter()
    {
        return [
			'TM_BLOCKNEWSLETTER_FOOTER' => [
                'title' => $this->l('Show blocknewsletter footer block'),
                'desc'  => $this->l('If enabled, shows newsletters subscribe form in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
		
            'TM_BLOCKCATEGORIES_FOOTER' => [
                'title' => $this->l('Show blockcategories footer block'),
                'desc'  => $this->l('If enabled, shows category tree block in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
            'TM_BLOCKCMS_FOOTER' => [
                'title' => $this->l('Show blockcms footer block'),
                'desc'  => $this->l('If enabled, shows Informations links in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_BLOCKMYACCOUNT_FOOTER' => [
                'title' => $this->l('Show myaccount footer block'),
                'desc'  => $this->l('If enabled, shows MyAccount links in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_COPYRIGHT_CONTENT' => [
                'title' => $this->l('Copyright footer text'),
                'desc'  => $this->l('Text to be displayed in the copyright footer block.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed. Enter &amp;copy; for copyright symbol.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
		];
    } 
	protected function getOptionFieldsColors()
    {
        return [	
			'TM_BACKGROUND_BODY_COLOR' => [
                'title' => $this->l('Body background color'),
                'desc'  => $this->l('Choose the background color for the body of pages.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_BODY_COLOR', 
				'size' => 7, 
            ],						
		];
    } 	
	
	protected function getOptionFieldsHeaderColors()
    {
        return [
			'TM_SLOGAN_CONTENT_COLOR' => [
                'title' => $this->l('Top page Slogan text color'),
                'desc'  => $this->l('Choose the slogan text color in the top of pages.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_SLOGAN_CONTENT_COLOR', 
				'size' => 7, 
            ],	
			'TM_BACKGROUND_SLOGAN_COLOR' => [
                'title' => $this->l('Top page Slogan background color'),
                'desc'  => $this->l('Choose the background color in the top for slogan.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_SLOGAN_COLOR', 
				'size' => 7, 
            ],
			'TM_UNDERMENU_CONTENT_COLOR' => [
                'title' => $this->l('Top page under menu text slider color'),
                'desc'  => $this->l('Choose the slogan text color in the top of pages.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_UNDERMENU_CONTENT_COLOR', 
				'size' => 7, 
            ],	
			'TM_BACKGROUND_UNDERMENU_COLOR' => [
                'title' => $this->l('Top page under menu text slider background color'),
                'desc'  => $this->l('Choose the background color in the top for slogan.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_UNDERMENU_COLOR', 
				'size' => 7, 
            ],
			'TM_UNDERMENU_OPACITY' => [
                'title' => $this->l('Top page under menu opacity'),
                'desc'  => $this->l('Choose the transparence for that submenu bar.'),
				'type' 	=> 'integer', 
				'name' => 'TM_UNDERMENU_OPACITY', 
				'cast' => 'strval',
				'type' => 'select',
				'list' => $this->getOpacity(),	
				'identifier' => 'value',
            ],	
			'TM_BACKGROUND_NAVBAR_COLOR' => [
                'title' => $this->l('Header Navbar background color'),
                'desc'  => $this->l('Choose the background color in the top navbar.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_NAVBAR_COLOR', 
				'size' => 7, 
            ],
			'TM_BACKGROUND_HEADER_COLOR' => [
                'title' => $this->l('Header background color'),
                'desc'  => $this->l('Choose the background color in the header.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_HEADER_COLOR', 
				'size' => 7, 
            ],			
		];
    } 	
	
	protected function getOptionFieldsContentColors()
    {
        return [
			'TM_BACKGROUND_CONTENT_COLOR' => [
                'title' => $this->l('Main page content background color'),
                'desc'  => $this->l('Choose the background color in the main container.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_CONTENT_COLOR', 
				'size' => 7, 
            ],		
			'TM_PRODLISTBLOCKBG_COLOR' => [
                'title' => $this->l('Product list Block background color'),
                'desc'  => $this->l('Choose the background color for each product block on list.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_PRODLISTBLOCKBG_COLOR', 
				'size' => 7, 
            ],				
		];
    } 	
	
	protected function getOptionFieldsFooterColors()
    {
        return [	
			'TM_BACKGROUND_FOOTER_COLOR' => [
                'title' => $this->l('Footer background color'),
                'desc'  => $this->l('Choose the background color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_BACKGROUND_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_TEXT_FOOTER_COLOR' => [
                'title' => $this->l('Footer text color'),
                'desc'  => $this->l('Choose the text color in the footer. Mainly for copyright'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_TEXT_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_TEXT_FOOTER_TITLE_COLOR' => [
                'title' => $this->l('Footer title color'),
                'desc'  => $this->l('Choose the Titles block color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_TEXT_FOOTER_TITLE_COLOR', 
				'size' => 7, 
            ],			
			'TM_LINK_FOOTER_COLOR' => [
                'title' => $this->l('Footer links color'),
                'desc'  => $this->l('Choose the links color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_LINK_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_LINKHOVER_FOOTER_COLOR' => [
                'title' => $this->l('Footer links hover color'),
                'desc'  => $this->l('Choose the links hover color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_LINKHOVER_FOOTER_COLOR', 
				'size' => 7, 
            ],		
		];
    } 	

	protected function getOptionFieldsOrder()
    {
        return [
			'TM_QTTYINCART_ON' => [
                'title' => $this->l('Show stock quantity in cart'),
                'desc'  => $this->l('On the Summary shopping cart page, show the number of each products remaining in stock.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_GENDERTITLE_OFF' => [
                'title' => $this->l('Gender title hidden'),
				'desc'  => $this->l('Don\'t ask for the gender title on the creation account form'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_BIRTHDAY_OFF' => [
                'title' => $this->l('Birthday hidden in the form'),
				'desc'  => $this->l('Don\'t ask for the birth date on the creation account form'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
		];
    } 

	protected function getOptionFieldsMobileOrder()
    {
        return [
			'TM_ORDERHEADING_OFF' => [
                'title' => $this->l('Hide step titles on order for mobile'),
				'desc'  => $this->l('On the Orders steps, hide titles for each step for mobile version.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
		];
    } 	
			
    /**
     * Registers a module hook
     *
     * @param string $module
     * @param string $hook
     *
     * @return bool
     * @throws Adapter_Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function hookModule($module, $hook)
    {
        $module = Module::getInstanceByName($module);

        return $module->registerHook($hook);
    }

	/**
     * Adds assets to page header
     * and passes configuration variables to smarty
     */
    public function hookDisplayHeader()
    {
        $idLang = (int) $this->context->language->id;

		static $copyrightContent = [];
        if (!isset($copyrightContent[$idLang])) {
            try {
                $copyrightContent[$idLang] = Configuration::get('TM_COPYRIGHT_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $copyrightContent[$idLang] = '';
            }
        }
		
		$showprodqty_incart = Configuration::get('TM_QTTYINCART_ON');	
		if ($showprodqty_incart == '1') {
			$showqty = true;
		} 
		else {
			$showqty = false;
		}
		
        $this->context->smarty->assign(
            [
                'ctheme' => [
                    'footer' => [
                        'copyright' => [
                            'display' => true,
                            'html'    => $copyrightContent[$idLang],
                        ],
                    ],
					'product_line' => [
							'quantity' => [
								'display' => $showqty,
							],
						],
                ],
            ]
        );
		        
		/* Show Products Quantity on summary shopping cart page */
		
	/* 	if ($showprodqty_incart == '1') {

					'ctheme' => [
						'product_line' => [
							'quantity' => [
								'display' => true,
							],
						],
					],
				]
			);
		} */
		
		$this->context->controller->addCSS($this->_path.'/views/css/colors-'.$this->context->shop->id.'.css');	
		$this->context->controller->addJS($this->_path.'/views/js/tmcustom-'.$this->context->shop->id.'.js');
    }
	
	/**
     * Adds assets to page Banner Hook
     * and passes configuration variables to smarty
     */
    public function hookDisplayBanner()
    {
        $idLang = (int) $this->context->language->id;
        
		static $sloganContent = [];
        if (!isset($sloganContent[$idLang])) {
            try {
                $sloganContent[$idLang] = Configuration::get('TM_SLOGAN_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $sloganContent[$idLang] = '';
            }
        }
		
		$this->context->smarty->assign(array(
			'slogan' => $sloganContent[$idLang]
		));
        
		return $this->display(__FILE__, 'tmslogan.tpl');		
    }

    /**
     * Adds assets to displayHome
     * and passes configuration variables to smarty
     */
    public function hookDisplayHome()
    {
        $idLang = (int) $this->context->language->id;
        
		static $homeContent = [];
        if (!isset($homeContent[$idLang])) {
            try {
                $homeContent[$idLang] = Configuration::get('TM_HOME_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $homeContent[$idLang] = '';
            }
        }
		
        $this->context->smarty->assign(array(
			'hometext' => $homeContent[$idLang],
		));	
			
		return $this->display(__FILE__, 'tmhome.tpl');			
    }
	

	public function hookDisplayNav()
    {
        $idLang = (int) $this->context->language->id;
        
		static $undermenuContent0 = [];
        if (!isset($undermenuContent[$idLang])) {
            try {
                $undermenuContent0[$idLang] = Configuration::get('TM_UNDERMENU0_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $undermenuContent0[$idLang] = '';$undermenuContent1[$idLang] = '';
            }
		}
		static $undermenuContent1 = [];
		if (!isset($undermenuContent1[$idLang])) {
			try {
                $undermenuContent1[$idLang] = Configuration::get('TM_UNDERMENU1_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                
            }
		}
		static $undermenuContent2 = [];
		if (!isset($undermenuContent2[$idLang])) {
			try {
                $undermenuContent2[$idLang] = Configuration::get('TM_UNDERMENU2_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $undermenuContent2[$idLang] = '';
            }
        }
		
		$this->context->smarty->assign(array(
			'undermenu0' => $undermenuContent0[$idLang],
			'undermenu1' => $undermenuContent1[$idLang],
			'undermenu2' => $undermenuContent2[$idLang],
		));
        
		return $this->display(__FILE__, 'tmundermenu.tpl');		
    }
	
	
	public function leftTab()
    {
        $html = '<div class="sidebar col-xs-12 col-lg-2"><ul class="nav nav-tabs">';
        foreach(self::$tabs AS $tab)
            $html .= '<li class="nav-item"><a href="javascript:;" title="'.$this->l($tab['name']).'" data-fieldset="'.$tab['id'].'">'.$this->l($tab['name']).'</a></li>';
        $html .= '</ul></div>';
        return $html;
    }
	
}
