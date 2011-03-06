<?php
class WebSocketHandshake 
{

    /*! Easy way to handshake a WebSocket via draft-ietf-hybi-thewebsocketprotocol-00
     * @link    http://www.ietf.org/id/draft-ietf-hybi-thewebsocketprotocol-00.txt
     * @author  Andrea Giammarchi
     * @blog    webreflection.blogspot.com
     * @date    4th June 2010
     * @example
     *          // via function call ...
     *          $handshake = WebSocketHandshake($buffer);
     *          // ... or via class
     *          $handshake = (string)new WebSocketHandshake($buffer);
     *
     *          socket_write($socket, $handshake, strlen($handshake));
     */

    private $__value__;

    public function __construct($buffer) 
    {
        $resource = $host = $origin = $key1 = $key2 = $protocol = $code = $handshake = null;
        preg_match('#GET (.*?) HTTP#', $buffer, $match) && $resource = $match[1];
        preg_match("#Host: (.*?)\r\n#", $buffer, $match) && $host = $match[1];
        preg_match("#Sec-WebSocket-Key1: (.*?)\r\n#", $buffer, $match) && $key1 = $match[1];
        preg_match("#Sec-WebSocket-Key2: (.*?)\r\n#", $buffer, $match) && $key2 = $match[1];
        preg_match("#Sec-WebSocket-Protocol: (.*?)\r\n#", $buffer, $match) && $protocol = $match[1];
        preg_match("#Origin: (.*?)\r\n#", $buffer, $match) && $origin = $match[1];
        preg_match("#\r\n(.*?)\$#", $buffer, $match) && $code = $match[1];
       
       if($key1 && $key2)
       {
        $this->__value__ =
            "HTTP/1.1 101 WebSocket Protocol Handshake\r\n".
            "Upgrade: WebSocket\r\n".
            "Connection: Upgrade\r\n".
            "Sec-WebSocket-Origin: {$origin}\r\n".
            "Sec-WebSocket-Location: ws://{$host}{$resource}\r\n".
            ($protocol ? "Sec-WebSocket-Protocol: {$protocol}\r\n" : "").
            "\r\n".
            $this->_createHandshakeThingy($key1, $key2, $code)
        ;
       }else
       {
       		$r=$h=$o=null;
	    	if(preg_match("/GET (.*) HTTP/"   ,$buffer,$match)) 
	    		$resource=$match[1];
	    		 
	    	if(preg_match("/Host: (.*)\r\n/"  ,$buffer,$match)) 
	    		$host=$match[1]; 
	    	
	    	if(preg_match("/Origin: (.*)\r\n/",$buffer,$match)) 
	    		$origin=$match[1];
	       	$this->__value__ = 
	       	"HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	                	"Upgrade: WebSocket\r\n" .
	                	"Connection: Upgrade\r\n" .
	                	"WebSocket-Origin: " . $origin . "\r\n" .
	                	"WebSocket-Location: ws://" . $host . $resource . "\r\n" .
	                	"\r\n";
       }
       
    }

    public function __toString() 
    {
        return $this->__value__;
    }
    
    private function _doStuffToObtainAnInt32($key) 
    {
        return preg_match_all('#[0-9]#', $key, $number) && preg_match_all('# #', $key, $space) ?
            implode('', $number[0]) / count($space[0]) :
            ''
        ;
    }

    private function _createHandshakeThingy($key1, $key2, $code) 
    {
        return md5(
            pack('N', $this->_doStuffToObtainAnInt32($key1)).
            pack('N', $this->_doStuffToObtainAnInt32($key2)).
            $code,
            true
        );
    }
}
?>