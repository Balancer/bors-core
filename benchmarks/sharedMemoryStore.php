<?php

class sharedMemoryStore {

	private $shmk_key;
	private $shm_id;
	private $var_key=1;
	private $lock_count = 0;

	var $sem_id;

	public function __construct($key="",$size=0,$perm=0666) {

		if ($key=="")
			$key=__FILE__;

		// default 16KB size shared memory
		if ($size==0)
			$size=1024*16;

		$this->shm_key=$key;

		$this->shm_key=ftok($key,'N');
		$this->shm_id=shm_attach($this->shm_key,$size,$perm);

		if ( empty($this->shm_id) )  {
			throw new Exception("shared memory allocation failed");
		}

		$this->sem_id=sem_get($this->shm_key,1,0666,true);

		if ( empty($this->sem_id) ) {
			throw new Exception("sem_get failed");
		}
	}

	public function lock()
	{
//echo "L?.".posix_getpid()." [{$this->lock_count}]\n";
		if($this->lock_count++)
			return;
//echo "L!.".posix_getpid()." [{$this->lock_count}]\n";
		if(!sem_acquire($this->sem_id))
			throw new Exception("lock failed");
	}

	public function unlock($ignore = false)
	{
//echo "U?.".posix_getpid()." [{$this->lock_count}]\n";
		if(--$this->lock_count > 0)
			return;
		if($this->lock_count < 0)
		{
			if(!$ignore)
				echo "[".posix_getpid()."]: unlock parinig error\n";
			return;
		}
//echo "U!.".posix_getpid()." [{$this->lock_count}]\n";
		if(!sem_release($this->sem_id))
			throw new Exception("unlock failed");
	}

	public function set($key,$value)
	{
		$this->lock();
		$res = shm_get_var($this->shm_id,$this->var_key);

		if($res === FALSE)
			$res=array();

		$res[$key]=$value;

		$ret = shm_put_var($this->shm_id, $this->var_key, $res);
		$this->unlock();

		if(!$ret)
			throw new Exception("shm_put_var failed");
	}

	public function get($key)
	{
		$res = shm_get_var($this->shm_id,$this->var_key);

		if($res === false)
		{
			echo "warn array empty\n";
			return false;
		}

		return @$res[$key];
	}

	public function incr($key,$increment=1)
	{
		$this->lock();

		$res=@shm_get_var($this->shm_id,$this->var_key);

		if ($res===FALSE)
			$res=array();

		if ( empty($res[$key]) )
			$res[$key]=0;

		$res[$key]+=$increment;

		if (!  shm_put_var($this->shm_id,$this->var_key,$res) ) {
			$this->unlock();
			throw new Exception("shm_put_var failed");
		}

		$this->unlock();

		return $res[$key];

	}

	public function  __destruct()
	{
		$this->unlock(true);
	}
}
