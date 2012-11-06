<?php

class Ups extends ShippingMethodAbstract
{
	const SANDBOX_URL = 'https://wwwcie.ups.com/ups.app/xml/Rate'
		,LIVE_URL = 'https://onlinetools.ups.com/ups.app/xml/Rate';

	const PICKUP_TYPE_DAILY_PICKUP = '01'
		, PICKUP_TYPE_CUSTOMER_COUNTER = '03'
		, PICKUP_TYPE_ONE_TIME_PICKUP = '06'
		, PICKUP_TYPE_ON_CALL_AIR = '07'
		, PICKUP_TYPE_LETTER_CENTER = '19'
		, PICKUP_TYPE_AIR_SERVICE_CENTER = '20';

	// Domestic
	const SERVICE_NEXT_DAY_AIR_EARLY_AM = '14'
		, SERVICE_NEXT_DAY_AIR = '01'
		, SERVICE_NEXT_DAY_AIR_SAVER = '13'
		, SERVICE_2ND_DAY_AIR_AM = '59'
		, SERVICE_2ND_DAY_AIR = '02'
		, SERVICE_3DAY_SELECT = '12'
		, SERVICE_GROUND = '03';

	// International
	const SERVICE_STANDARD = '11'
		, SERVICE_WORLDWIDE_EXPRESS = '07'
		, SERVICE_WORLDWIDE_EXPRESS_PLUS = '54'
		, SERVICE_WORLDWIDE_EXPEDITED = '08';

	const PACKAGE_TYPE_UNKNOWN = '00'
		, PACKAGE_TYPE_UPS_LETTER = '01'
		, PACKAGE_TYPE_PACKAGE = '02'
		, PACKAGE_TYPE_TUBE = '03'
		, PACKAGE_TYPE_PAK = '04'
		, PACKAGE_TYPE_EXPRESS_BOX = '21'
		, PACKAGE_TYPE_25KG_BOX = '24'
		, PACKAGE_TYPE_15KG_BOX = '25'
		, PACKAGE_TYPE_PALLET = '30';

	/**
	 * Checks is custom plugin parameters are set and valid;
	 * If no validation is needed, just return true;
	 * @return mixed
	 */
	public function isConfigured(&$error_message = 'msg_invalid_request')
	{
		if($error_message == '') $error_message = 'msg_invalid_request';
		if(!isset($this->gateway_api)) return false;
		if(!isset($this->username)) return false;
		if(!isset($this->password)) return false;
		if(!isset($this->shipper_number)) return false;
		if(!isset($this->api_access_key)) return false;
		return true;
	}

	/**
	 * Calculates shipping rates
	 *
	 * @param Cart    $cart             SHipping cart for which to calculate shipping
	 * @param Address $shipping_address Address to which products should be shipped
	 */
	public function calculateShipping(Cart $cart, Address $shipping_address = null)
	{
		if($shipping_address == null) return 0;

		$ups_api = new UpsAPI($this);
		$shipping_cost = $ups_api->getRate($shipping_address);
		return $shipping_cost;
	}
}

class UpsAPI extends APIAbstract
{
	private $ups_config;

	public function __construct(Ups $ups_config)
	{
		$this->ups_config = $ups_config;
	}

	public function getRate(Address $shipping_address)
	{
		$data = $this->getAccessRequestXML(
		  	$this->ups_config->api_access_key
			, $this->ups_config->username
			, $this->ups_config->password
		);
		$data .= $this->getRatingServiceSelectionRequestXML(
			$this->ups_config
			, $shipping_address
		);

		if($this->ups_config->gateway_api == Ups::SANDBOX_URL)
		{
			// If using sandbox, skip SSL verify, otherwise all requests fail with:
			// "Error [60]: SSL certificate problem, verify that the CA cert is OK"
			$response = $this->request($this->ups_config->gateway_api, $data, true);
		}
		else
		{
			$response = $this->request($this->ups_config->gateway_api, $data);
		}

		$RatingServiceSelectionResponse = simplexml_load_string($response);

		if($RatingServiceSelectionResponse->Response->ResponseStatusCode === 0)
		{
			$error = $RatingServiceSelectionResponse->Response->Error;
			throw new APIException("UPS Error: [ErrorSeverity] - " . $error->ErrorSeverity . "; [ErrorCode] - " . $error->ErrorCode . '; [Error description] - ' . $error->ErrorDescription);
		}

		$shipping_cost = $RatingServiceSelectionResponse->RatedShipment->TransportationCharges->Monetary->Value;
		return $shipping_cost;
	}


	public function getAccessRequestXML($access_license_number, $userid, $userpassword)
	{
		return "<?xml version=\"1.0\" ?>
			<AccessRequest xml:lang='en-US'>
				<AccessLicenseNumber>
					$access_license_number
				</AccessLicenseNumber>
				<UserId>
					$userid
				</UserId>
				<Password>
					$userpassword
				</Password>
			</AccessRequest>";
	}

	public function getRatingServiceSelectionRequestXML($ups_config, Address $shipping_address)
	{
		return "<?xml version=\"1.0\" ?>
			<RatingServiceSelectionRequest>
				<Request>
					<TransactionReference>
						<CustomerContext>Rating and Service</CustomerContext>
						<XpciVersion>1.0</XpciVersion>
					</TransactionReference>
					<RequestAction>Rate</RequestAction>
					<RequestOption>Rate</RequestOption>
				</Request>
				<PickupType>
					<Code>$ups_config->pickup_type</Code>
				</PickupType>
				<Shipment>
					<Shipper>
						<ShipperNumber>$ups_config->shipper_number</ShipperNumber>
						<Address>
							<PostalCode>$ups_config->postal_code</PostalCode>
							<CountryCode>$ups_config->country_code</CountryCode>
						</Address>
					</Shipper>
					<ShipTo>
						<CompanyName>$shipping_address->company</CompanyName>
						<AttentionName>$shipping_address->firstname $shipping_address->lastname</AttentionName>
						<PhoneNumber>$shipping_address->telephone</PhoneNumber>
						<Address>
							<AddressLine1>$shipping_address->address</AddressLine1>
							<AddressLine2 />
							<AddressLine3 />
							<City>$shipping_address->city</City>
							<PostalCode>$shipping_address->postal_code</PostalCode>
							<CountryCode>$shipping_address->country</CountryCode>
						</Address>
					</ShipTo>
					<Service><Code>$ups_config->service</Code></Service>
					<Package>
						<PackagingType>
							<Code>$ups_config->package_type</Code>
						</PackagingType>
						<!-- Dimensions are required if Packaging Type if not Letter, Express Tube,
						or Express Box; Required for GB to GB and Poland to Poland shipments -->
						<!-- <Dimensions>
							<UnitOfMeasurement>
								<Code>CM</Code>
							</UnitOfMeasurement>
							<Length>$length</Length>
							<Width>$width</Width>
							<Height>$height</Height>
						</Dimensions>-->
						<!-- Weight allowed for letters/envelopes.-->
						<!--<PackageWeight>
    						<UnitOfMeasurement>
    							<Code>KGS</Code>
    						</UnitOfMeasurement>
    						<Weight>2</Weight>
    					</PackageWeight>-->
					</Package>
					<ShipmentServiceOptions />
				</Shipment>
			</RatingServiceSelectionRequest>";


	}


}