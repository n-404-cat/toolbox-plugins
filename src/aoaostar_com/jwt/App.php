<?php

namespace plugin\aoaostar_com\jwt;

use Firebase\JWT\Key;
use plugin\Drive;

class App implements Drive
{
    # 访问/api/jwt
    public function Index()
    {
        $token = request()->param('token', '');
        $key = request()->param('key', '');
        $algorithm = request()->param('algorithm', 'HS256');
        
        if (empty($token)) {
            return error('请输入JWT令牌');
        }
        
        try {
            $result = $this->decodeJwt($token, $key, $algorithm);
            return success($result);
        } catch (\Exception $e) {
            return error('解析失败：' . $e->getMessage());
        }
    }
    
    # JWT解码
    private function decodeJwt($token, $key = '', $algorithm = 'HS256')
    {
        // 分割JWT令牌
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \Exception('无效的JWT令牌格式');
        }
        
        $header = $this->decodePart($parts[0]);
        $payload = $this->decodePart($parts[1]);
        $signature = $parts[2];
        
        // 如果提供了密钥，验证签名
        if (!empty($key)) {
            try {
                \Firebase\JWT\JWT::$leeway = 60;
                $decoded = \Firebase\JWT\JWT::decode($token, new Key($key, $algorithm));
                $payload = (array)$decoded;
            } catch (\Exception $e) {
                // 签名验证失败，但仍返回解析结果，只是标记签名无效
                $payload['signature_valid'] = false;
            }
        }
        
        // 处理时间字段，添加格式化时间信息
        $payload = $this->processTimeFields($payload);
        
        return [
            'header' => $header,
            'payload' => $payload,
            'signature' => $signature,
            'signature_valid' => isset($payload['signature_valid']) ? $payload['signature_valid'] : null
        ];
    }
    
    # 解码JWT部分
    private function decodePart($part)
    {
        $json = base64_decode(str_replace(['-', '_'], ['+', '/'], $part));
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('无效的JWT部分：' . json_last_error_msg());
        }
        return $data;
    }
    
    # 处理时间字段
    private function processTimeFields($data)
    {
        $timeFields = ['iat', 'exp', 'nbf'];
        
        foreach ($data as $key => $value) {
            if ((in_array($key, $timeFields) && is_numeric($value)) || (is_numeric($value) && strpos($key, 'time') !== false)) {
                // 检查是否为毫秒时间戳
                $timestamp = (int)$value;
                $isMilliseconds = false;
                
                // 如果是13位数字，可能是毫秒时间戳
                if (strlen((string)$value) === 13) {
                    $timestamp = (int)($value / 1000);
                    $isMilliseconds = true;
                }
                
                // 添加格式化时间信息
                $data[$key] = [
                    'value' => $value,
                    'timestamp' => $timestamp,
                    'formatted' => date('Y-m-d H:i:s', $timestamp),
                    'is_milliseconds' => $isMilliseconds
                ];
            } elseif (is_array($value)) {
                // 递归处理嵌套数组
                $data[$key] = $this->processTimeFields($value);
            }
        }
        
        return $data;
    }
    
    # 生成JWT令牌
    public function Generate()
    {
        $payload = request()->param('payload', []);
        $key = request()->param('key', '');
        $algorithm = request()->param('algorithm', 'HS256');
        
        // 如果payload是字符串，尝试解析为JSON
        if (is_string($payload)) {
            $payload = json_decode($payload, true);
        }
        
        if (empty($payload) || !is_array($payload)) {
            return error('请输入有效的payload数据');
        }
        
        if (empty($key)) {
            return error('请输入密钥');
        }
        
        try {
            $jwt = \Firebase\JWT\JWT::encode($payload, $key, $algorithm);
            return success(['token' => $jwt]);
        } catch (\Exception $e) {
            return error('生成失败：' . $e->getMessage());
        }
    }
    
    # 验证JWT令牌
    public function Validate()
    {
        $token = request()->param('token', '');
        $key = request()->param('key', '');
        $algorithm = request()->param('algorithm', 'HS256');
        
        if (empty($token)) {
            return error('请输入JWT令牌');
        }
        
        if (empty($key)) {
            return error('请输入密钥');
        }
        
        try {
            \Firebase\JWT\JWT::$leeway = 60;
            $decoded = \Firebase\JWT\JWT::decode($token, new Key($key, $algorithm));
            return success(['valid' => true, 'data' => (array)$decoded]);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return success(['valid' => false, 'message' => '签名不正确']);
        } catch (\Firebase\JWT\BeforeValidException $e) {
            return success(['valid' => false, 'message' => 'token尚未生效']);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return success(['valid' => false, 'message' => 'token已过期']);
        } catch (\Exception $e) {
            return success(['valid' => false, 'message' => '未知错误：' . $e->getMessage()]);
        }
    }
}