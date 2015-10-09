<?php

/********************
 *
 * AdvancedSort - Complex SQL-like sorting for arrays and objects
 *
 * 2015 by David Buchanan - http://joesvolcano.net/
 *
 * GitHub: https://github.com/unlox775/AdvancedSort-php
 *
 ********************/

/**
 * sort_arrays() - Sorts an array of assoc arrays by the named keys(s)
 *
 * This is a quick way to sort a bunch of arrays by named keys.
 * It can do complex sorts off multiple fields and do DESC sorts
 * as well.
 *
 * @param string $array     The source array to sort
 * @param string $criteria  a single field name, an array of fields to sort by, or an array of 2-dimensional arrays: (fieldname, bool do_descending or 'ASC'/'DESC')
 * @return string
 */
function &sort_arrays($array, $criteria) { $a = sort_objects($array, $criteria);  return $a; }
/**
 * sort_objects() - Sorts an array of objects by object property(s)
 *
 * This is a quick way to sort a bunch of objects by object
 * properties.  It can do complex sorts off multiple fields and
 * do DESC sorts as well.
 *
 * Caveat: When at all possible use database-side sorts, like is
 * supported by the {@link StarkORM} relations system.  This
 * sort engine is rather expensive...
 *
 * @param string $array     The source array to sort
 * @param string $criteria  a single field name, an array of fields to sort by, or an array of 2-dimensional arrays: (fieldname, bool do_descending or 'ASC'/'DESC')
 * @return string
 */
function &sort_objects($array, $criteria) {
    //START_TIMER('sort_objects', SORT_PROFILE);

    if ( ! is_array($criteria) ) $criteria = array( $criteria );
    AdvancedSort::$__sort_objects_criteria = array();
    foreach ( (array) $criteria as $crit ) {
    	AdvancedSort::$__sort_objects_criteria[] = (
    		is_array($crit)
    		? array( $crit[0], (! empty($crit[1]) && strtoupper($crit[1]) !== 'ASC' ))
    		: array( $crit, false )
    		);
    }

    $first = reset($array);
    @usort($array,(
    	is_array($first)
    	? array('AdvancedSort','__sort_arrays_sorter')
    	: array('AdvancedSort','__sort_objects_sorter')
    	));

    //END_TIMER('sort_objects', SORT_PROFILE);
    return $array;
}

class AdvancedSort {
	public static $__sort_objects_criteria = null;

	public static function __sort_objects_sorter($a,$b) {
	    foreach (AdvancedSort::$__sort_objects_criteria as $crit) {
			$by_method = false;
			if ( substr($crit[0], -2) == '()' ) { $col = substr($crit[0], 0, strlen($crit[0])-2);  $by_method = true; }
	        else $col = $crit[0];
			if ( $by_method )
				$cmp  = ( $crit[1] ) ? strnatcasecmp( $b->$col(), $a->$col() ) : strnatcasecmp( $a->$col(), $b->$col() );
	        else $cmp = ( $crit[1] ) ? strnatcasecmp( $b->$col,   $a->$col   ) : strnatcasecmp( $a->$col,   $b->$col   );

	        if ( $cmp != 0 ) return $cmp;
	    }
	    return 0;
	}

	public static function __sort_arrays_sorter($a,$b) {
	    foreach (AdvancedSort::$__sort_objects_criteria as $crit) {
	        $col = $crit[0];
	        $cmp = ( $crit[1] ) ? strnatcasecmp( $b[$col], $a[$col] ) : strnatcasecmp( $a[$col], $b[$col] );
	        if ( $cmp != 0 ) return $cmp;
	    }
	    return 0;
	}
}
