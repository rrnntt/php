<?php

require_once('TextProblem.php');

/**
 * Creates a problem from a type and an xml content strings.
 */
class ProblemFactory
{
	/**
	 * Creates a Problem.
	 * @param $type Problem type: 'text', 'equation', ... ?
	 * @param $content An xml string with the problem content. The format depends on the $type.
	 */
	public static function create($type, $content)
	{
		if ( $type == 'text' )
		{
			return ProblemFactory::createText( $content );
		}
		
		throw new Exception("Problem type $type is undefined.");
	}
	
	/**
	 * Creates a 'text'-type problem.
	 * @param $content The content xml string: 
	 *   '<problem><text>Problem question.</text><answer>The answer.</answer></problem>'.
	 */
	private static function createText($content)
	{
		$doc = new DOMDocument();
		$doc->loadXML( $content );
		//echo $doc->saveXML();
		$nodeList = $doc->getElementsByTagName('text');
		if ( $nodeList->length > 0 )
		{
			$txt = $nodeList->item(0)->textContent;
		}
		else
		{
			throw new Exception("Cannot create text problem: <text> tag not found.");
		}
		$nodeList = $doc->getElementsByTagName('answer');
		if ( $nodeList->length > 0 )
		{
			$ans = $nodeList->item(0)->textContent;
		}
		else
		{
			throw new Exception("Cannot create text problem: <answer> tag not found.");
		}
		$prob = new TextProblem;
		$prob->setText( $txt );
		$prob->answer = $ans;
		return $prob;
	}
}

?>
