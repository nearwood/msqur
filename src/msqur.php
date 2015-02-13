<?php
/*
 * @brief Public API here?
 * 
 * Defines the actions taken at the user level:
 * upload
 * browse
 * view
 * etc.
 * 
 * @see http://www.aljtmedia.com/blog/creating-a-php-rest-routing-class-for-your-application/
 */
class Msqur
{
	private MsqurDB db;
	
	function __construct()
	{
		MsqurDB db = new MsqurDB(); //TODO check reuse
	}
	
	public function getMSQ()
	{
	}
	
	public function putMSQ()
	{
	}
	
	
}

/*
Msqur msqur = new msqur();
Msq msq = msqur.upload("blah");

//browse
list<Msq> msqs = browse()/browse(START_NUMBER) //have to check with angular how data pagination/ordering can work best

//search
*/
?>
