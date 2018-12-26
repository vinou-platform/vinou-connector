mod.wizards.newContentElement.wizardItems {
	vinou {
		header = Vinou Plugins
		elements {
            wines {
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wines.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wines.description
                icon = ../typo3conf/ext/vinou_connector/Resources/Public/Icons/wines.svg
                tt_content_defValues {
                    CType = list
                    list_type = vinou_wines
                }
            }
            enquiry {
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.enquiry.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.enquiry.description
                icon = ../typo3conf/ext/vinou_connector/Resources/Public/Icons/enquiry.svg
                tt_content_defValues {
                    CType = list
                    list_type = vinou_enquiry
                }
            }
            shop {
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.shop.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.shop.description
                icon = ../typo3conf/ext/vinou_connector/Resources/Public/Icons/shop.svg
                tt_content_defValues {
                    CType = list
                    list_type = vinou_shop
                }
            }
        }
    	show := addToList(wines,enquiry,shop)
	}
}