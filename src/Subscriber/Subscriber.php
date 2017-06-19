<?php
	
	namespace Welp\MailchimpBundle\Subscriber;
	use twentysteps\Commons\EnsureBundle\Ensure;
	
	/**
	 * Class to represent a subscriber
	 * http://developer.mailchimp.com/documentation/mailchimp/reference/lists/members/
	 */
	class Subscriber
	{
		/**
		 * Subscriber's email
		 * @var string
		 */
		protected $email;
		
		/**
		 * Subscriber's merge fields
		 * @var array
		 */
		protected $mergeFields;
		
		/**
		 * Subscriber's options
		 * @var array
		 */
		protected $options;
		
		/**
		 *
		 * @param string $email
		 * @param array $mergeFields
		 * @param array $options
		 */
		public function __construct($email, array $mergeFields = [], array $options = [])
		{
			$this->email = $email;
			$this->mergeFields = $mergeFields;
			$this->options = $options;
		}
		
		/**
		 * Format Subscriber for MailChimp API request
		 * @return array
		 */
		public function formatMailChimp()
		{
			$options = $this->options;
			if (!empty($this->getMergeFields())) {
				$options = array_merge([
					'merge_fields' => $this->getMergeFields()
				], $options);
			}
			
			$rtn= array_merge([
				'email_address' => $this->getEmail()
			], $options);
			return $rtn;
		}
		
		/**
		 * Correspond to email_address in MailChimp request
		 * @return string
		 */
		public function getEmail()
		{
			return $this->email;
		}
		
		/**
		 * Set mergefields
		 * @param array $mergeFields
		 * @return array ['TAGKEY' => value, ...]
		 */
		public function setMergeFields(array $mergeFields)
		{
			// since fev2017, MailChimp API doesn't handle null value en throw 400
			// errors when you try to add subscriber with a mergefields value to null
			foreach ($mergeFields as $key => $value) {
				if ($value == null) {
					unset($mergeFields[$key]);
				}
			}
			$this->mergeFields = $mergeFields;
			
			return $this->mergeFields;
		}
		
		/**
		 * Correspond to merge_fields in MailChimp request
		 * @return array ['TAGKEY' => value, ...]
		 */
		public function getMergeFields()
		{
			// since fev2017, MailChimp API doesn't handle null value en throw 400
			// errors when you try to add subscriber with a mergefields value to null
			foreach ($this->mergeFields as $key => $value) {
				if ($value === null) {
					unset($this->mergeFields[$key]);
				}
			}
			return $this->mergeFields;
		}
		
		/**
		 * The rest of member options:
		 * email_type, interests, language, vip, location, ip_signup, timestamp_signup, ip_opt, timestamp_opt
		 * @return array
		 */
		public function getOptions()
		{
			return $this->options;
		}
		
		/**
		 * @param $key
		 * @param null $default
		 * @return mixed|null
		 */
		public function getMergeFieldValue($key,$default=null) {
			Ensure::isTrue(array_key_exists($key,$this->mergeFields),sprintf("merge field [%s] does not exist",$key));
			$value=$this->mergeFields[$key];
			if ($value===null) {
				return $default;
			}
			return $value;
		}
		
		public  function mb_str_split($str, $split_length) {
			$chars = array();
			$len = mb_strlen($str, 'UTF-8');
			for ($i = 0; $i < $len; $i+=$split_length ) {
				$chars[] = mb_substr($str, $i, $split_length, 'UTF-8');
			}
			return $chars;
		}
		
		public function setMergeFieldValue($key,$value,$chunkCount=null,$delimiter=null) {
			if (!$chunkCount) {
				$this->mergeFields[$key]=$value;
			} else {
				if (!$delimiter) {
					if ($value) {
						$chunks = $this->mb_str_split($value,230);
						$count = count($chunks);
					} else {
						$chunks = null;
						$count = 0;
					}
					for ($i=1; $i<=$chunkCount; $i++) {
						if ($count>=$i) {
							$this->mergeFields[$key.'_'.$i]=$chunks[$i-1];
						} else {
							$this->mergeFields[$key.'_'.$i]='';
						}
					}
				} else {
					if ($value) {
						$chunks = explode($delimiter, $value);
						$count = count($chunks);
					} else {
						$chunks = null;
						$count = 0;
					}
					for ($i=1; $i<=$chunkCount; $i++) {
						if ($count>=$i) {
							$this->mergeFields[$key.'_'.$i]=$chunks[$i-1];
						} else {
							$this->mergeFields[$key.'_'.$i]='';
						}
					}
				}
			}
		}
	}
