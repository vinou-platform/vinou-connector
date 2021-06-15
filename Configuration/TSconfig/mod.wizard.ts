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
            wineries {
                iconIdentifier = extension-vinouconnector-wineries
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wineries.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.wineries.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_wineries
                }
            }
            merchants {
                iconIdentifier = extension-vinouconnector-merchants
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.merchants.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.merchants.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_merchants
                }
            }
            client {
                iconIdentifier = extension-vinouconnector-client
                title = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.client.title
                description = LLL:EXT:vinou_connector/Resources/Private/Language/locallang.xlf:be.client.description
                tt_content_defValues {
                    CType = list
                    list_type = vinouconnector_client
                }
            }
        }
    	show := addToList(wines,products,bundles,shop,office,wineries,merchants,client)
	}
}