<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
 

class AckStatus{

	const ACK = 200;
	const REJECT = 300;
	const CANCEL = 400;
	const REJECT_OUT = 500;
	const RECOVERTRUE = 600;
	const RECOVERFALSE = 700;
 
}