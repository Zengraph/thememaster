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

        $hooksToInstall = ['displayHeader', 'displayHome', 'displayBanner'];
        foreach ($hooksToInstall as $hookName) {
            $this->registerHook($hookName);
        }

        // Translatable configuration items
        foreach (Language::getLanguages(false) as $language) {
            $idLanguage = (int) $language['id_lang'];
            Configuration::updateValue(
                'TM_CFG_HOME_CONTENT',
                [
                    $idLanguage => 'Welcome on our online shopping website',
                ]
            );
			Configuration::updateValue(
                'TM_CFG_SLOGAN_CONTENT',
                [
                    $idLanguage => 'Enjoy shopping in our online boutique',
                ]
            );
			Configuration::updateValue(
                'TM_CFG_COPYRIGHT_CONTENT',
                [
                    $idLanguage => '&copy; Acme Corporation 2020',
                ]
            );
        }	
		
		// Colors configuration default
		Configuration::updateValue('TM_CFG_BACKGROUND_BODY_COLOR', '#ffffff');
		Configuration::updateValue('TM_CFG_BACKGROUND_SLOGAN_COLOR', '#ffffff');
		Configuration::updateValue('TM_CFG_SLOGAN_CONTENT_COLOR', '#333333');
		Configuration::updateValue('TM_CFG_BACKGROUND_NAVBAR_COLOR', '#f4f4f4');
		Configuration::updateValue('TM_CFG_BACKGROUND_HEADER_COLOR', '#ffffff');
		Configuration::updateValue('TM_CFG_BACKGROUND_CONTENT_COLOR', '#ffffff');	
		Configuration::updateValue('TM_CFG_BACKGROUND_FOOTER_COLOR', '#121212');
		Configuration::updateValue('TM_CFG_TEXT_FOOTER_COLOR', '#c8c8c8');
		Configuration::updateValue('TM_CFG_TEXT_FOOTER_TITLE_COLOR', '#ffffff');
		Configuration::updateValue('TM_CFG_LINK_FOOTER_COLOR', '#c8c8c8');
		Configuration::updateValue('TM_CFG_LINKHOVER_FOOTER_COLOR', '#ff6f61');
		
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
			'TM_CFG_HOME_CONTENT',
			'TM_CFG_SLOGAN_CONTENT',
			'TM_CFG_SLOGAN_ALIGN',
			'TM_CFG_BACKGROUND_SLOGAN_COLOR',
			'TM_CFG_SLOGAN_CONTENT_COLOR',
			'TM_CFG_SLOGAN_CONTENT_WEIGHT',
			'TM_CFG_BACKGROUND_BODY_COLOR',
			'TM_CFG_BACKGROUND_HEADER_COLOR',		
			'TM_CFG_BACKGROUND_CONTENT_COLOR',	
			'TM_CFG_BLOCKNEWSLETTER_FOOTER',
            'TM_CFG_BLOCKCATEGORIES_FOOTER',
			'TM_CFG_BLOCKCMS_FOOTER',
			'TM_CFG_BLOCKMYACCOUNT_FOOTER',
            'TM_CFG_COPYRIGHT_CONTENT',
			'TM_CFG_BACKGROUND_NAVBAR_COLOR',
			'TM_CFG_BACKGROUND_FOOTER_COLOR',
			'TM_CFG_TEXT_FOOTER_COLOR',
			'TM_CFG_TEXT_FOOTER_TITLE_COLOR',
			'TM_CFG_LINK_FOOTER_COLOR',
			'TM_CFG_LINKHOVER_FOOTER_COLOR',
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
        foreach ($this->getOptionFieldsGeneral() as $key => $field) {	
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
            } else {
                $value = Tools::getValue($key);
                if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
                    $value = call_user_func($field['cast'], $value);
                }
                $values[$key] = $value;	
            }
            Configuration::updateValue($key, $values[$key], $htmlAllowed);
        }
		
		foreach ($this->getOptionFieldsHeader() as $key => $field) {
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
            } else {
                $value = Tools::getValue($key);
                if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
                    $value = call_user_func($field['cast'], $value);
                }
                $values[$key] = $value;	
            }
            Configuration::updateValue($key, $values[$key], $htmlAllowed);
        }
		
		foreach ($this->getOptionFieldsFooter() as $key => $field) {
				
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
            } else {
                $value = Tools::getValue($key);
                if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
                    $value = call_user_func($field['cast'], $value);
                }

                $values[$key] = $value;	
            }
            Configuration::updateValue($key, $values[$key], $htmlAllowed);
        }
		
		foreach ($this->getOptionFieldsColors() as $key => $field) {	
            $htmlAllowed = isset($field['html']) && $field['html'];
			$value = Tools::getValue($key);
            if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
				$value = call_user_func($field['cast'], $value);
            }
            $values[$key] = $value;	
            Configuration::updateValue($key, $values[$key], $htmlAllowed);	
	
		}
		
		foreach ($this->getOptionFieldsHeaderColors() as $key => $field) {	
            $htmlAllowed = isset($field['html']) && $field['html'];
			$value = Tools::getValue($key);
            if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
				$value = call_user_func($field['cast'], $value);
            }
            $values[$key] = $value;	
            Configuration::updateValue($key, $values[$key], $htmlAllowed);				
			
		}
		
		foreach ($this->getOptionFieldsContentColors() as $key => $field) {	
            $htmlAllowed = isset($field['html']) && $field['html'];
			$value = Tools::getValue($key);
            if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
				$value = call_user_func($field['cast'], $value);
            }
            $values[$key] = $value;	
            Configuration::updateValue($key, $values[$key], $htmlAllowed);		
						
		}
		
		foreach ($this->getOptionFieldsFooterColors() as $key => $field) {	
            $htmlAllowed = isset($field['html']) && $field['html'];
			$value = Tools::getValue($key);
            if (isset($field['cast']) && $field['cast'] && in_array($field['cast'], $castFunctions)) {
				$value = call_user_func($field['cast'], $value);
            }
            $values[$key] = $value;	
            Configuration::updateValue($key, $values[$key], $htmlAllowed);	
		}
		
		// Start to fill CSS file 
		$css = '';
		
		if ($values['TM_CFG_CONTAINERFULL_CONTENT']) {$css .='.container { width: calc(100% - 30px); }
#columns.container { width: 100%; }
';} 		
		
		$backgroundbodycolor = Configuration::get('TM_CFG_BACKGROUND_BODY_COLOR');
		$backgroundheadercolor = Configuration::get('TM_CFG_BACKGROUND_SLOGAN_COLOR');
		$backgroundheadercolor = Configuration::get('TM_CFG_SLOGAN_CONTENT_COLOR');		
		$backgroundnavbarcolor = Configuration::get('TM_CFG_BACKGROUND_NAVBAR_COLOR');	
		$backgroundheadercolor = Configuration::get('TM_CFG_BACKGROUND_HEADER_COLOR');
		$backgroundcontentcolor = Configuration::get('TM_CFG_BACKGROUND_CONTENT_COLOR');		
		$backgroundfootercolor = Configuration::get('TM_CFG_BACKGROUND_FOOTER_COLOR');	
		$textfootercolor = Configuration::get('TM_CFG_TEXT_FOOTER_COLOR');
		$footertitlecolor =	Configuration::get('TM_CFG_TEXT_FOOTER_TITLE_COLOR');
		$footerlinkcolor = Configuration::get('TM_CFG_LINK_FOOTER_COLOR');
		$footerlinkhovercolor =	Configuration::get('TM_CFG_LINKHOVER_FOOTER_COLOR');		
	
		/* Write a CSS file to modify colors */
$css  .= 'body { background-color:'.$backgroundbodycolor.'; }	
.navbar.navbar-default { background-color:'.$backgroundnavbarcolor.'; }		
#header, #header-blocks { background-color:'.$backgroundheadercolor.'; }	
.top_column_wrapper, #columns { background-color:'.$backgroundcontentcolor.'; }
#footer { background-color:'.$backgroundfootercolor.'; }
#footer, #copyright-footer { color:'.$textfootercolor.'; }
#footer .footer-title, #footer .title_block { color:'.$footertitlecolor.'; }
#footer a { color:'.$footerlinkcolor.'; }
#footer a:hover { color:'.$footerlinkhovercolor.'; }
';

		/* Adding more Css code for Slogan */
		$sloganbackgroundcolor = Configuration::get('TM_CFG_BACKGROUND_SLOGAN_COLOR');
		$slogancontentcolor = Configuration::get('TM_CFG_SLOGAN_CONTENT_COLOR');	
		$css .= '#slogan { background: '.$sloganbackgroundcolor.'}';
		$css .= '#slogan p { color: '.$slogancontentcolor.'; padding: 5px 15px 7px;	margin-bottom: 0px; ';
		$sloganalign = Configuration::get('TM_CFG_SLOGAN_ALIGN');	
		if ($sloganalign == '0') $css .= 'text-align: left;';
		if ($sloganalign == '1') $css .= 'text-align: center;';
		elseif ($sloganalign == '2') $css .= 'text-align: right;';
		$slogancontentweight = Configuration::get('TM_CFG_SLOGAN_CONTENT_WEIGHT');
		if ($slogancontentweight == '0') $css .= 'font-weight: 100;';	
		elseif ($slogancontentweight == '1') $css .= 'font-weight: 400;';
		elseif ($slogancontentweight == '2') $css .= 'font-weight: 700;';
		elseif ($slogancontentweight == '3') $css .= 'font-weight: 900;';

		$css .= '}
		';

		/* Adding more css for Home text */
		$css .= '#hometext { text-align: center; padding: 15px; margin: 30px 0 20px; font-size: 150%; }
		';
						
		@chmod( '../modules/thememaster/views/css/colors-'.$this->context->shop->id.'.css',0777);
        $xml2 = fopen('../modules/thememaster/views/css/colors-'.$this->context->shop->id.'.css','w');
        fwrite($xml2,$css);
		/* end of CSS file script */
		
		if ($values['TM_CFG_BLOCKNEWSLETTER_FOOTER']) {
            $this->hookModule('blocknewsletter', 'footer');
        } else {
            $this->unhookModule('blocknewsletter', 'footer');
        }
		
		if ($values['TM_CFG_BLOCKCATEGORIES_FOOTER']) {
            $this->hookModule('blockcategories', 'footer');
        } else {
            $this->unhookModule('blockcategories', 'footer');
        }
		
		if ($values['TM_CFG_BLOCKCMS_FOOTER']) {
            $this->hookModule('blockcms', 'footer');
        } else {
            $this->unhookModule('blockcms', 'footer');
        }
		
		if ($values['TM_CFG_BLOCKMYACCOUNT_FOOTER']) {
            $this->hookModule('blockmyaccountfooter', 'footer');
        } else {
            $this->unhookModule('blockmyaccountfooter', 'footer');
        }
    }

    /**
     * Return HelperOptions fields that are used in module configuration form.
     *
     * @return array
     */
	protected function getOptionFieldsGeneral()
    {
        return [
            'TM_CFG_HOME_CONTENT' => [
                'title' => $this->l('Home page content'),
                'desc'  => $this->l('Text to be displayed in the home page.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_CFG_CONTAINERFULL_CONTENT' => [
                'title' => $this->l('Container full width mode'),
                'desc'  => $this->l('Full width or boxed version.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			
		];
    } 
	
	protected function getOptionFieldsHeader()
    {
        return [
            'TM_CFG_SLOGAN_CONTENT' => [
                'title' => $this->l('Header text in top of page'),
                'desc'  => $this->l('Text to be displayed in the top of pages as a slogan.').' '.$this->l('Leave empty to not display the block.'),
                'hint'  => $this->l('HTML is allowed.'),
                'cast'  => 'strval',
                'type'  => 'textareaLang',
                'html'  => true,
                'size'  => 50,
            ],
			'TM_CFG_SLOGAN_CONTENT_WEIGHT' => [
                'title' => $this->l('Header text font weight'),
				'hint'  => $this->l('Some weights might not be available with your selected fonts.'),
				'name' => 'TM_CFG_SLOGAN_CONTENT_WEIGHT',
				'type' => 'radio',
				'choices' => [
					$this->l('light'),
					$this->l('normal'),
					$this->l('bold'),
					$this->l('bolder')
				],
                'validation' => 'isUnsignedInt',
			],
			'TM_CFG_SLOGAN_ALIGN' => [
                'title' => $this->l('Header text alignment in top of page'),
                'desc'  => $this->l('Alignment of the slogan.'),
				'hint'  => $this->l('Select the best alignment for slogan in top of pages.'),
				'name' => 'TM_CFG_SLOGAN_ALIGN',
				'type' => 'radio',
				'choices' => [
					$this->l('left align'),
					$this->l('centered'),
					$this->l('right align')
				],
                'validation' => 'isUnsignedInt',
			],
		];
    } 
	protected function getOptionFieldsFooter()
    {
        return [
			'TM_CFG_BLOCKNEWSLETTER_FOOTER' => [
                'title' => $this->l('Show blocknewsletter footer block'),
                'desc'  => $this->l('If enabled, shows newsletters subscribe form in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
		
            'TM_CFG_BLOCKCATEGORIES_FOOTER' => [
                'title' => $this->l('Show blockcategories footer block'),
                'desc'  => $this->l('If enabled, shows category tree block in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
            'TM_CFG_BLOCKCMS_FOOTER' => [
                'title' => $this->l('Show blockcms footer block'),
                'desc'  => $this->l('If enabled, shows Informations links in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_CFG_BLOCKMYACCOUNT_FOOTER' => [
                'title' => $this->l('Show myaccount footer block'),
                'desc'  => $this->l('If enabled, shows MyAccount links in the footer.'),
                'cast'  => 'boolval',
                'type'  => 'bool',
            ],
			'TM_CFG_COPYRIGHT_CONTENT' => [
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
			'TM_CFG_BACKGROUND_BODY_COLOR' => [
                'title' => $this->l('Body background color'),
                'desc'  => $this->l('Choose the background color for the body of pages.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_BODY_COLOR', 
				'size' => 7, 
            ],				
		];
    } 	
	
	protected function getOptionFieldsHeaderColors()
    {
        return [
			'TM_CFG_SLOGAN_CONTENT_COLOR' => [
                'title' => $this->l('Top page Slogan text color'),
                'desc'  => $this->l('Choose the slogan text color in the top of pages.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_SLOGAN_CONTENT_COLOR', 
				'size' => 7, 
            ],	
			'TM_CFG_BACKGROUND_SLOGAN_COLOR' => [
                'title' => $this->l('Top page Slogan background color'),
                'desc'  => $this->l('Choose the background color in the top for slogan.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_SLOGAN_COLOR', 
				'size' => 7, 
            ],
			'TM_CFG_BACKGROUND_NAVBAR_COLOR' => [
                'title' => $this->l('Header Navbar background color'),
                'desc'  => $this->l('Choose the background color in the top navbar.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_NAVBAR_COLOR', 
				'size' => 7, 
            ],
			'TM_CFG_BACKGROUND_HEADER_COLOR' => [
                'title' => $this->l('Header background color'),
                'desc'  => $this->l('Choose the background color in the header.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_HEADER_COLOR', 
				'size' => 7, 
            ],			
		];
    } 	
	
	protected function getOptionFieldsContentColors()
    {
        return [
			'TM_CFG_BACKGROUND_CONTENT_COLOR' => [
                'title' => $this->l('Main page content background color'),
                'desc'  => $this->l('Choose the background color in the main container.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_CONTENT_COLOR', 
				'size' => 7, 
            ],			
		];
    } 	
	
	protected function getOptionFieldsFooterColors()
    {
        return [	
			'TM_CFG_BACKGROUND_FOOTER_COLOR' => [
                'title' => $this->l('Footer background color'),
                'desc'  => $this->l('Choose the background color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_BACKGROUND_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_CFG_TEXT_FOOTER_COLOR' => [
                'title' => $this->l('Footer text color'),
                'desc'  => $this->l('Choose the text color in the footer. Mainly for copyright'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_TEXT_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_CFG_TEXT_FOOTER_TITLE_COLOR' => [
                'title' => $this->l('Footer title color'),
                'desc'  => $this->l('Choose the Titles block color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_TEXT_FOOTER_TITLE_COLOR', 
				'size' => 7, 
            ],			
			'TM_CFG_LINK_FOOTER_COLOR' => [
                'title' => $this->l('Footer links color'),
                'desc'  => $this->l('Choose the links color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_LINK_FOOTER_COLOR', 
				'size' => 7, 
            ],
			'TM_CFG_LINKHOVER_FOOTER_COLOR' => [
                'title' => $this->l('Footer links hover color'),
                'desc'  => $this->l('Choose the links hover color in the footer.'),
				'validation' => 'isColor',
				'type' 	=> 'color', 
				'name' => 'TM_CFG_LINKHOVER_FOOTER_COLOR', 
				'size' => 7, 
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
                $copyrightContent[$idLang] = Configuration::get('TM_CFG_COPYRIGHT_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $copyrightContent[$idLang] = '';
            }
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
                ],
            ]
        );
		
		$this->context->controller->addCSS($this->_path.'/views/css/colors-'.$this->context->shop->id.'.css');	
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
                $sloganContent[$idLang] = Configuration::get('TM_CFG_SLOGAN_CONTENT', $idLang);
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
                $homeContent[$idLang] = Configuration::get('TM_CFG_HOME_CONTENT', $idLang);
            } catch (PrestaShopException $e) {
                $homeContent[$idLang] = '';
            }
        }
		
        $this->context->smarty->assign(array(
			'hometext' => $homeContent[$idLang],
		));	
			
		return $this->display(__FILE__, 'tmhome.tpl');			
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
