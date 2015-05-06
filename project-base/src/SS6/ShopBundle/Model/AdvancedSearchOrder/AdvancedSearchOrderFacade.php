<?php

namespace SS6\ShopBundle\Model\AdvancedSearchOrder;

use SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderConfig;
use SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFormFactory;
use SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderService;
use SS6\ShopBundle\Model\Order\Listing\OrderListAdminFasade;
use SS6\ShopBundle\Model\Product\Listing\ProductListAdminFacade;
use Symfony\Component\HttpFoundation\Request;

class AdvancedSearchOrderFacade {

	const RULES_FORM_NAME = 'as';

	/**
	 * @var \SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderConfig
	 */
	private $advancedSearchOrderConfig;

	/**
	 * @var \SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderFormFactory
	 */
	private $advancedSearchOrderFormFactory;

	/**
	 * @var \SS6\ShopBundle\Model\AdvancedSearchOrder\AdvancedSearchOrderService
	 */
	private $advancedSearchOrderService;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Listing\ProductListAdminFacade
	 */
	private $productListAdminFacade;

	private $orderListAdminFacade;

	public function __construct(
		AdvancedSearchOrderConfig $advancedSearchOrderConfig,
		AdvancedSearchOrderFormFactory $advancedSearchOrderFormFactory,
		AdvancedSearchOrderService $advancedSearchOrderService,
		ProductListAdminFacade $productListAdminFacade,
		OrderListAdminFasade $orderListAdminFasade
	) {
		$this->advancedSearchOrderConfig = $advancedSearchOrderConfig;
		$this->advancedSearchOrderFormFactory = $advancedSearchOrderFormFactory;
		$this->advancedSearchOrderService = $advancedSearchOrderService;
		$this->productListAdminFacade = $productListAdminFacade;
		$this->orderListAdminFacade = $orderListAdminFasade;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\Form\Form
	 */
	public function createAdvancedSearchOrderForm(Request $request) {
		$rulesData = (array)$request->get(self::RULES_FORM_NAME, null, true);
		$rulesFormData = $this->advancedSearchOrderService->getRulesFormViewDataByRequestData($rulesData);

		return $this->advancedSearchOrderFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesFormData);
	}

	/**
	 * @param string $filterName
	 * @return \Symfony\Component\Form\Form
	 */
	public function createRuleForm($filterName, $index) {
		$rulesData = [
			$index => $this->advancedSearchOrderService->createDefaultRuleFormViewData($filterName),
		];

		return $this->advancedSearchOrderFormFactory->createRulesForm(self::RULES_FORM_NAME, $rulesData);
	}

	/**
	 * @param array $advancedSearchOrderData
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilderByAdvancedSearchOrderData($advancedSearchOrderData) {
		$queryBuilder = $this->orderListAdminFacade->getOrderListQueryBuilder();
		$this->advancedSearchOrderService->extendQueryBuilderByAdvancedSearchOrderData($queryBuilder, $advancedSearchOrderData);

		return $queryBuilder;
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return bool
	 */
	public function isAdvancedSearchOrderFormSubmitted(Request $request) {
		$rulesData = $request->get(self::RULES_FORM_NAME);

		return $rulesData !== null;
	}

}
