mod.wizards.newContentElement.wizardItems {
	vinou {
		header = Vinou Plugins
		elements {
            wines {
                iconIdentifier = extension-vinouconnector-wines
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wines.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wines.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_wines
                }
            }
            products {
                iconIdentifier = extension-vinouconnector-products
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.products.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.products.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_products
                }
            }
            bundles {
                iconIdentifier = extension-vinouconnector-bundles
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.bundles.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.bundles.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_bundles
                }
            }
            facebook {
                iconIdentifier = extension-vinouconnector-facebook
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.facebook.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.facebook.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_facebook
                }
            }
            enquiry {
                iconIdentifier = extension-vinouconnector-enquiry
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.enquiry.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.enquiry.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_enquiry
                }
            }
            shop {
                iconIdentifier = extension-vinouconnector-shop
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.shop.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.shop.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_shop
                }
            }
            office {
                iconIdentifier = extension-vinouconnector-office
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.office.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.office.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_office
                }
            }
        }
    	show := addToList(wines,products,bundles,facebook,enquiry,shop,office)
	}
}