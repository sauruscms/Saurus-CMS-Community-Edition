<?php
/**
 * This source file is is part of Saurus CMS content management software.
 * It is licensed under MPL 1.1 (http://www.opensource.org/licenses/mozilla1.1.php).
 * Copyright (C) 2000-2010 Saurused Ltd (http://www.saurus.info/).
 * Redistribution of this file must retain the above copyright notice.
 * 
 * Please note that the original authors never thought this would turn out
 * such a great piece of software when the work started using Perl in year 2000.
 * Due to organic growth, you may find parts of the software being
 * a bit (well maybe more than a bit) old fashioned and here's where you can help.
 * Good luck and keep your open source minds open!
 * 
 * @package		SaurusCMS
 * @copyright	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */



class email {

	var $subject;
	var $message;
	var $html;
	var $attachment;
	var $attachments; // same as $attachment, only array of attachments
	var $charset;
	var $cid;
	var $sent_items;
	var $EOL;
	var $encodeSubject = true;
	var $size;

	function email() {
		if(func_num_args()>0) {
			$args = func_get_arg(0);
			$this->subject = $args[subject];
			$this->message = $args[message];
			$this->html = $args[html];
			$this->attachment = $args[attachment];
			$this->attachments = $args['attachments'];
			$this->charset = ($args[charset])?$args[charset]:'iso-8859-1';
			$this->cid = $args[cid];
		}

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->EOL = "\r\n";
		}
		else
		{
			$this->EOL = "\n";
		}
	}

	#######################################
	# This qp_encodes HTML mail and splits
	# it up in 75 char lines
	 function qp_encoding($Message) {

		/* Build (most polpular) Extended ASCII Char/Hex MAP (characters >127 & <255) */
		for ($i=0; $i<127; $i++) {
		$CharList[$i] = "/".chr($i+128)."/";
		$HexList[$i] = "=".strtoupper(bin2hex(chr($i+128)));
		}

		/* Encode equal sign & 8-bit characters as equal signs followed by their hexadecimal values */
		$Message = str_replace("=", "=3D", $Message);
		$Message = preg_replace($CharList, $HexList, $Message);

		/* Lines longer than 76 characters (size limit for quoted-printable Content-Transfer-Encoding)
		   will be cut after character 75 and an equals sign is appended to these lines. */
		$MessageLines = split("\n", $Message);
		$Message_qp = "";
		while(list(, $Line) = each($MessageLines)) {
		if (strlen($Line) > 75) {
		$Pointer = 0;
		while ($Pointer <= strlen($Line)) {
		$Offset = 0;
		if (preg_match("/^=(3D|([8-9A-F]{1}[0-9A-F]{1}))$/", substr($Line, ($Pointer+73), 3))) $Offset=-2;
		if (preg_match("/^=(3D|([8-9A-F]{1}[0-9A-F]{1}))$/", substr($Line, ($Pointer+74), 3))) $Offset=-1;
		$Message_qp.= substr($Line, $Pointer, (75+$Offset))."=\n";
		if ((strlen($Line) - ($Pointer+75)) <= 75) {
		$Message_qp.= substr($Line, ($Pointer+75+$Offset))."\n";
		break 1;
		}
		$Pointer+= 75+$Offset;
		}
		} else {
		$Message_qp.= $Line."\n";
		}
		}
		return $Message_qp;
	}

	#######################################
	# This encodes the file to base64 and
	# splits it up in 75 char lines
	function attachment_encoding($Message) {
		return $Message = chunk_split(base64_encode($Message));
	}

	#######################################
	# Send formated mail with Attachments
	# & HTML
	function send_mail() {
		//Get ARGS
			if(func_num_args()>0) {
				$args = func_get_arg(0);
				$from = $args[from];
				$to = $args[to];
				$bcc = $args['bcc'];
				$this->subject = ($args[subject])?$args[subject]:$this->subject;
				$this->message = ($args[message])?$args[message]:$this->message;
				$this->html = ($args[html])?$args[html]:$this->html;
				$this->attachment = ($args[attachment])?$args[attachment]:$this->attachment;
				$this->attachments = ($args['attachments'] ? $args['attachments'] : $this->attachments);
				$this->charset = ($args[charset])?$args[charset]:$this->charset;
				$this->cid = ($args[cid])?$args[cid]:$this->cid;
				$this->encodeSubject = ($args['dont_encode_subject'] ? false : true);
				$multipart = ($this->attachment['size'] || (is_array($this->attachments) && count($this->attachments)));
			}

			if($from && $to) {
			###################
			# THE Magic stuff

				$boundary = md5(time());

				$html = $this->qp_encoding($this->html);

				$headers ='MIME-Version: 1.0'.$this->EOL;
				$headers.='From: '.$this->encodeHeader($from, $this->charset).$this->EOL;
				$headers.='Reply-To: '.$this->encodeHeader($from, $this->charset).$this->EOL;
				$headers.='Return-Path: '.$this->encodeHeader($from, $this->charset).$this->EOL;
				$headers.='Bcc: '.$this->encodeHeader($bcc, $this->charset).$this->EOL;
				$headers.='X-Mailer: PHP Mailer'.$this->EOL;

				$Msg = '';

				// it seems best to send everything out as a multipart message, then the concent-types work without special headers
				$headers.= "Content-Type: multipart/".($multipart ? ($this->cid?'related' : 'mixed'):'alternative') . ";\n boundary=\"" . $boundary . "\"" . $this->EOL;
				$headers .= 'This is a multi-part message in MIME format.';

				if($this->message) {
					//only print this out if theres a text part
					$Msg.= '--'.$boundary."\n";
					$Msg.= "Content-Type: text/plain; charset=\"".$this->charset."\"\n";
					$Msg.= "Content-Transfer-Encoding: 8bit\n";
					$Msg.= "\n";

					$Msg.=$this->message."\n\n";
				}

				if($this->html) {
					// print out html
					$Msg.= '--'.$boundary."\n";
					$Msg.= "Content-Type: text/html; charset=".$this->charset."\n";
					$Msg.= "Content-Transfer-Encoding: quoted-printable\n"; #Bug #2445
					$Msg.= "\n";

					$Msg.=$html."\n\n";
				}

				if($multipart) {
					// now we add attachments (images, etc)

					// add single attachment, bc-compat
					if($this->attachment['size'])
					{
						$file = $this->attachment['tmp_name'];

						if (file_exists($file) || $this->attachment['file_contents']) {
							if($this->attachment['file_contents'])
							{
								$attach = $this->attachment['file_contents'];
							}
							elseif ($fd = fopen($file, "rb")) {
								$attach = fread ($fd, filesize($file));
								fclose ($fd);
							}
							$Msg.= '--'.$boundary."\n";
							$Msg.= "Content-Type: ".$this->attachment[type]."; \n name=\"".$this->attachment[name]."\"\n";
							$Msg.= "Content-disposition: attachment;\n filename=\"".$this->attachment[name]."\"\n";
							$Msg.= "Content-Transfer-Encoding: base64\n";
							$Msg.= "Content-ID: <".$this->cid.">\n";
							$Msg.= "\n";

							$Msg.= $this->attachment_encoding($attach);

							$Msg.= "\n\n";
						}
					} // endif add single attachment, bc-compat
					// add multiple attachments
					if((is_array($this->attachments) && count($this->attachments)))
					{
						foreach($this->attachments as $attachment) if($attachment['size'])
						{
							$file = $attachment['tmp_name'];

							if (file_exists($file) || $attachment['file_contents'])
							{
								if($attachment['file_contents'])
								{
									$attach = $attachment['file_contents'];
								}
								elseif ($fd = fopen($file, "rb")) {
									$attach = fread ($fd, filesize($file));
									fclose ($fd);
								}

								$Msg.= '--'.$boundary."\n";
								$Msg.= "Content-Type: ".$attachment['type']."; \n\tname=\"".$attachment['name']."\"\n";
								$Msg.= "Content-disposition: attachment;\n\tfilename=\"".$attachment['name']."\"\n";
								$Msg.= "Content-Transfer-Encoding: base64\n";
								$Msg.= "Content-ID: <".$this->cid.">\n";
								$Msg.= "\n";

								$Msg.= $this->attachment_encoding($attach);

								$Msg.= "\n\n";
							}
						} // end foreach
					} // endif add multiple attachments

					$Msg.= '--'.$boundary."--";
				}

				if($this->encodeSubject)
				{
					$this->subject = $this->encodeHeader($this->subject, $this->charset);
				}
				else
				{
					$this->subject = $this->subject;
				}

				$to = $this->encodeHeader($to, $this->charset);

				return mail($to, $this->subject, $Msg, $headers);
			}
	}

	#######################################
	# Check e-mail format and MX-records
	# returns "1" - if mail ok, "0" - otherwise
	function check_mail_mx($mail) {
		$mailok=0;
			if (eregi("^[_\.0-9a-z-]+@([0-9a-z][-0-9a-z\.]+)\.([a-z]{2,4}$)", $mail, $check)) {
				if (getmxrr($check[1].".".$check[2],$tmp)) {$mailok=1;}
			}
		return $mailok;
	}


    /**
    * Function to encode a header if necessary
    * according to RFC2047
    */
    function encodeHeader($input, $charset = 'UTF-8')
    {
        preg_match_all('/(\s?\w*[\x80-\xFF]+\w*\s?)/', $input, $matches);
        foreach ($matches[1] as $value) {
            $replacement = preg_replace('/([\x20\x80-\xFF])/e', '"=" . strtoupper(dechex(ord("\1")))', $value);
            $input = str_replace($value, '=?' . $charset . '?Q?' . $replacement . '?=', $input);
        }

        return $input;
    }


} //end class
