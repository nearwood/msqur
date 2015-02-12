<?php
/*
 * @brief Public API here?
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
list<Msq> msqs = browse(5);

//search
*/
?>
