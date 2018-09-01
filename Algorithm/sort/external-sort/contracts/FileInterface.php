<?php

interface FileInterface
{
	public function read();
	public function write($file);
}