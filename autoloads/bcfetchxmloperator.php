<?php

/*!
  \Class   BCFetchXmlOperator bcfetchxmloperator.php
  \ingroup eZTemplateOperators
  \brief   Handles template operator bcfetchxml. You can pass bcfetchxml a url and it will use REST to retrieve an object containing a calendar. This was first developed for STDL. Added optional caching of xml file.
  \version 1.1.0
  \date    Tuesday June 14 2008 12:10:00 pm
  \author  Brookins Consulting

  Example:
\code
{def $events = bcfetchxml($node.object.data_map.url.data_text)|wash}
{def $events_cached = bcfetchxml($node.object.data_map.url.data_text, true())|wash}
{def $events_debug = bcfetchxml($node.object.data_map.url.data_text, true(), true() )|wash}
\endcode
*/

class BCFetchXmlOperator
{
    var $Debug = false;
    var $Cache = true;

    // var $DefaultMethod = 'parseSTDLXML';
    // var $DefaultMethod = 'parseWEBSVNXML';
    var $DefaultMethod = 'parseGITXML';

    /*!
      Constructor, does nothing by default.
    */
    function __construct()
    {
    }

    /*!
     \return an array with the template operator name.
    */
    function operatorList()
    {
        return array( 'fetchxml' );
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array( 'fetchxml' => array( 'first_param' => array( 'type' => 'string',
                                                                         'required' => true,
                                                                         'default' => 'default text' ),
						 'second_param' => array( 'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => true ),
						 'third_param' => array( 'type' => 'string',
                                                                         'required' => false,
                                                                         'default' => false )
                                                 ) );
    }


    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters, $placement )
    {
	$defaultMethod = (string)$this->DefaultMethod;
	$operatorMethodNameValue = (string)$defaultMethod;
        $firstParam = $namedParameters['first_param'];
        $secondParam = $namedParameters['second_param'];
        $thirdParam = $namedParameters['third_param'];
        if( $secondParam == false or $secondParam == '' or $secondParam == null ) {
	    $secondParam = false;
	    $this->Cache = false;
	} else {
	    $secondParam = true;
	    $this->Cache = true;
        }
        if( $thirdParam == true ) {
	    $thirdParam = true;
	    $this->Debug = true;
	} else {
	    $thirdParam = false;
	    $this->Debug = false;
        }
        switch ( $operatorName )
        {
            case 'fetchxml':
            {
		if( $defaultMethod == 'parseSTDLXML' ) {
			$operatorValue = $this->$operatorMethodNameValue($firstParam, $secondParam, $thirdParam);
		} elseif( $defaultMethod == 'parseWEBSVNXML' ) {
			$operatorValue = $this->$operatorMethodNameValue($firstParam, $secondParam, $thirdParam);
		} elseif( $defaultMethod == 'parseGITXML' ) {
			$operatorValue = $this->$operatorMethodNameValue($firstParam, $secondParam, $thirdParam);
		} else {
			$operatorValue = $this->parseXML($firstParam, $secondParam, $thirdParam);
		}
            } break;
        }
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function parseXML($url, $cache = true, $debug = false )
    {
	$this->Debug = $debug;
	$xmlEvents = false;
	if( $this->Cache == true && $cache == true )
        {
	    $xmlEvents = $this->cachedRemoteXMLCall( $url, $cache );
        } else {
	    $xmlEvents = $this->remoteXMLCall( $url );
        }
        foreach($xmlEvents as $xmlEvent) {
	    foreach($xmlEvent as $key => $value) {
	 	$key = (string)$key;
		$value = (string)$value;
		$event[$key] = $value;
		// echo "Key: " . $key . " Value: " . $value . "<br />\n";
	     }
	     // var_dump( $event );
 	     $events[] = $event;
         }
 	if ( $this->Debug == true )
	    eZDebug::writeDebug( "bcfetchxml: parseXML, remote url call results count: " . print_r( count( $events ), TRUE) );
	    // eZDebug::writeDebug( "bcfetchxml: parseXML, remote url call results: " . print_r( $events, TRUE) );
            // var_dump( $events );
         return $events;
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function parseSTDLXML( $url, $cache = true, $debug = false )
    {
        $this->Debug = $debug;
        $xmlEvents = false;
        if( $this->Cache == true && $cache == true )
        {
            if ( $this->Debug == true )
                eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", Caching Enabled." . print_r( false , TRUE) );

            $xmlEvents = $this->cachedRemoteXMLCall( $url, $cache );
        } else {
            $xmlEvents = $this->remoteXMLCall( $url );
        }
        foreach($xmlEvents as $xmlEvent) {
            foreach($xmlEvent as $key => $value) {
                $key = (string)$key;
                $value = (string)$value;
                $event[$key] = $value;
                // echo "Key: " . $key . " Value: " . $value . "<br />\n";
             }
             // var_dump( $event );
             $events[] = $event;
         }
        if ( $this->Debug == true )
            eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results count: " . print_r( count( $events ), TRUE) );
            // eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $events, TRUE) );
            // var_dump( $events );
         return $events;
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function parseWEBSVNXML( $url, $cache = true, $debug = false )
    {
	$this->Debug = $debug;
	$xmlEvents = false;
	if( $this->Cache == true && $cache == true )
        {
	    if ( $this->Debug == true )
	        eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", Caching Enabled." . print_r( false , TRUE) );

	    $xmlEvents = $this->cachedRemoteXMLCall( $url, $cache );
        } else {
	    $xmlEvents = $this->remoteXMLCall( $url );
        }
        foreach($xmlEvents as $xmlEvent) {
	    foreach($xmlEvent as $key => $value) {
	        
	 	$key = (string)$key;
		// $value = (string)$value;

		if ( $key != 'item' ) {
			$event[$key] = (string)$value;
		} else {
		        if( isset( $event[$key] ) && is_array( $event[$key] ) ) {
  			    // array_push( $event[$key], $value );
			    foreach( $value as $in => $out) {
			       $subkey = (string)$in;
			       $subvalue = (string)$out[0];
			       // var_dump( $value);
			       $subitem[$subkey] = $subvalue;
			    }
  			    array_push( $event[$key], $subitem );
			} else {
			    $event[$key] = array();
			    $subitem=array();
			    foreach( $value as $in => $out) {
			       $subkey = (string)$in;
			       $subvalue = (string)$out[0];
			       // var_dump( $value);
			       $subitem[$subkey] = $subvalue;
			    }
  			    array_push( $event[$key], $subitem );
			}
		}
		// echo "Key: " . $key . " Value: " . $value . "<br />\n";
	     }
	     // var_dump( $event );
 	     $events[] = $event;
         }
 	if ( $this->Debug == true ) {
	    eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results count: " . print_r( count( $events[0]['item'] ), TRUE) );
	    // eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $subitem, TRUE) );
	    eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $events, TRUE) );
	    // eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $xmlEvents, TRUE) );
	}
         // var_dump( $events );
         return $events;
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function parseGITXML( $url, $cache = true, $debug = false )
    {
	$this->Debug = $debug;
	$xmlEvents = false;

	$event = false; // $event = array();
        $events = false;

	if( $this->Cache == true && $cache == true )
        {
	    if ( $this->Debug == true )
	        eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", Caching Enabled." . print_r( false , TRUE) );

	    $xmlEvents = $this->cachedRemoteXMLCall( $url, $cache );
        } else {
	    $xmlEvents = $this->remoteXMLCall( $url );
        }
	// return $xmlEvents;

        foreach($xmlEvents as $xmlEvent) {
//	print_r($xmlEvent);
	    foreach($xmlEvent as $key => $value) {
	        // print_r( $key ); echo '<hr>';
	 	$key = (string)$key;
		// $value = (string)$value;

		if ( $key != 'item' ) {
		    if( $key != 'link' ){
			$event[$key] = (string)$value;
		    } else {
		           $attribs = (array)$xmlEvent->link->attributes();
			   $link = $attribs["@attributes"]["href"];
		    	   // print_r( $link ); echo '<hr />';
			   $event[$key] = (string)$link;
		    }
		} else {
		        if( isset( $event[$key] ) && is_array( $event[$key] ) ) {
  			    // array_push( $event[$key], $value );
			    foreach( $value as $in => $out) {
			       $subkey = (string)$in;
			       $subvalue = (string)$out[0];
			       // var_dump( $value);
			       $subitem[$subkey] = $subvalue;
			    }

  			    array_push( $event[$key], $subitem );
			} else {
			    if( $value != '' ) {
			    $event[$key] = array();
			    $subitem=array();
			    foreach( $value as $in => $out) {
			       $subkey = (string)$in;
			       $subvalue = (string)$out[0];
			       // var_dump( $value);
			       $subitem[$subkey] = $subvalue;
			    }

  			    array_push( $event[$key], $subitem );
			    }
			}
		}

		// echo "Key: " . $key . " Value: " . $value . "<br />\n";
	     }
	     // var_dump( $event );
	     if( $event != null )
 	     $events[] = $event;
         }
 	if ( $this->Debug == true ) {
	    eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results count: " . print_r( count( $events ), TRUE) );
	    // eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $subitem, TRUE) );
	    eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $events, TRUE) );
	    // eZDebug::writeDebug( "bcfetchxml: ".$this->DefaultMethod.", remote url call results: " . print_r( $xmlEvents, TRUE) );
	}
         // var_dump( $events );
         return $events;
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function remoteXMLCall( $url )
    {
	$config = eZINI::instance( 'site.ini' );
	$cacheTime = intval( $config->variable( 'RSSSettings', 'CacheTime' ) );

        $currentSiteAccessName = $GLOBALS['eZCurrentAccess']['name'];
        $feedFileName=str_replace('&', '_',  $url );
        $xmlEvents = false;
	$events = array();
	$event = array();

	// Load without cache
        if ( $this->Debug == true )
             eZDebug::writeDebug( "bcfetchxml: remoteXMLCall, remote url call: " . $currentSiteAccessName .", ". print_r( $url, TRUE) );

	// Suppress errors because this will fail sometimes
        $xmlEvents = simplexml_load_file( $url ); //replace with reference to URL attribute from Link object
	// print_r( $xmlEvents );

 	if( !$xmlEvents )
	{
            // try a second time in case the server is busy
            $xmlEvents = simplexml_load_file( $url );
            if( !$xmlEvents )
	    {
	 	if ( $this->Debug == true )
		    eZDebug::writeDebug( "bcfetchxml: remoteXMLCall remote url call failed: " . $currentSiteAccessName .", ". print_r( $url, TRUE) );

		return false;
	     }
        }
	return $xmlEvents;
    }

    // Parse the XML into event objects using PHP's SimpleXML library
    function cachedRemoteXMLCall( $url )
    {
	$config = eZINI::instance( 'site.ini' );
	$cacheTime = intval( $config->variable( 'RSSSettings', 'CacheTime' ) );
        $xmlEvents = false;

	if ( $cacheTime <= 0 )
	{
  	    // Load without cache
            $xmlEvents = $this->remoteXMLCall( $url );
            if( !$xmlEvents ) {
 		return false;
	     }
	     return $xmlEvents;
	}
	else
	{
            $feedName=str_replace('&', '_',  $url );
	    $events = array();
            $event = array();

	    // Load with cache
	    $cacheDir = eZSys::cacheDirectory();
	    $currentSiteAccessName = $GLOBALS['eZCurrentAccess']['name'];
	    $cacheFilePath = $cacheDir . '/rss/' . md5( $currentSiteAccessName . $feedName ) . '.xml';

	    if ( !is_dir( dirname( $cacheFilePath ) ) )
            {
	        eZDir::mkdir( dirname( $cacheFilePath ), false, true );
            }

 	    $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
	    if ( !$cacheFile->exists() or ( time() - $cacheFile->mtime() > $cacheTime ) )
	    {
                if ( $this->Debug == true )
	           eZDebug::writeDebug( "bcfetchxml: cachedRemoteXMLCall, remote url call: " . $currentSiteAccessName .", ". print_r( $url, TRUE) );

	        // Load without cache
        	$xmlEvents = $this->remoteXMLCall( $url );
	        if( !$xmlEvents ) {
 		    return false;
   	        } else {
		    $cacheFile->storeContents( $xmlEvents->asXML(), 'rsscache', 'xml' );
                    if ( $this->Debug == true )
		       eZDebug::writeDebug( "bcfetchxml: cachedRemoteXMLCall, refreshed cache for url call: " . $currentSiteAccessName .", ".print_r( $cacheFilePath, TRUE) );
                    }
             } else {
	         $xmlEventsString = $cacheFile->fetchContents();
		 $xmlEvents = simplexml_load_string( $xmlEventsString );
		 if ( $this->Debug == true )
		    eZDebug::writeDebug( "bcfetchxml: cachedRemoteXMLCall, loaded data for url call from cache: " . print_r($cacheFilePath, TRUE) );
		 }
	    }
	    return $xmlEvents;
	}
}

?>