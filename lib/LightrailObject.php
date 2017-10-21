<?php

namespace Lightrail;


class LightrailObject {

	private $properties;

	public function __construct( $properties = array() , $jsonRootName=null) {
		if ( isset( $jsonRootName ) ) {
			$properties = $properties[$jsonRootName];
		}
		$this->properties = array();
		$this->populate( $properties );
	}

	private function populate( $properties ) {
		foreach ( $properties as $name => $value ) {
			$this->create_property( $name, $value );
		}
	}

	private function create_property( $name, $value ) {
		$this->properties[ $name ] = is_array( $value ) ? $this->create_complex_property( $value )
			: $this->create_simple_property( $value );
	}

	private function create_complex_property( $value = array() ) {
		return new LightrailObject( $value );
	}

	private function create_simple_property( $value ) {
		return $value;
	}

	public function __get( $name ) {
		//$this->create_property_if_not_exists($name);
		return $this->properties[ $name ];
	}

	public function getRawJson() {
		return json_encode($this->properties);
	}

//	private function create_property_if_not_exists($name) {
//		if (array_key_exists($name, $this->properties)) return;
//		$this->create_property($name, array());
//	}

//	public function __set($name, $value) {
//		$this->create_property($name, $value);
//	}
}