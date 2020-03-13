<?php declare(strict_types=1);


namespace cjhswoftRabbitmqQueue;
 

class AckStatus{

	const ACK = 200; ///处理完成 出列
	const REJECT = 300;//拒绝  并且回列
	const CANCEL = 400;//删除当前的消费者
	const REJECT_OUT = 500;//拒绝 并且出列
	const RECOVERTRUE = 600;//回列 发送给新的consume
	const RECOVERFALSE = 700;//回列 发送给相同的consumer
	const BACK_TO_TAIL = 800;

 
}