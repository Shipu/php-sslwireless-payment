<?php

namespace Shipu\SslWPayment;

class Payment extends AbstractApi
{
    protected $config;
    protected $params = [];

    public function __construct( $config )
    {
        $this->config = $config;
        $this->preBuildParameter();
        parent::__construct();
    }

    public function paymentUrl()
    {
        return $this->client->baseUrl . 'gwprocess/v3/process.php';
    }

    public function customer( $info = [] )
    {
        $this->params = array_merge($this->params, $info);

        return $this;
    }

    public function amount( $amount )
    {
        $this->params[ 'total_amount' ] = $amount;

        return $this;
    }

    public function redirectUrl()
    {
        $redirectUrl = $this->config[ 'redirect_url' ];
        $this->params[ 'success_url' ] = $this->routeOrUrl($redirectUrl[ 'success' ]);
        $this->params[ 'fail_url' ] = $this->routeOrUrl($redirectUrl[ 'fail' ]);
        $this->params[ 'cancel_url' ] = $this->routeOrUrl($redirectUrl[ 'cancel' ]);

        return $this;
    }

    public function version( $version = '3.00' )
    {
        $this->params[ 'version' ] = $version;

        return $this;
    }

    public function generateTransaction()
    {
        $this->params[ 'tran_id' ] = uniqid(true) . microtime(true);

        return $this;
    }

    public function hiddenValue()
    {
        $formString = '';
        foreach ( $this->params as $name => $value ) {
            $formString .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />";
        }

        return $formString;
    }

    public function valid( $request, $amount = null, $transactionId = null )
    {
        if (
            $this->hashVerify($request) &&
            $this->statusFieldValidity($request) &&
            $this->sslCommerzValidationApiResponse($request, $amount, $transactionId)
        ) {
            return true;
        }

        return false;
    }

    public function validAmount( $request, $amount )
    {
        return $this->sslCommerzValidationApiResponse($request, $amount);
    }

    public function validTransactionId( $request, $transactionId )
    {
        return $this->sslCommerzValidationApiResponse($request, null, $transactionId);
    }


    private function hashVerify( $request )
    {
        $store_passwd = $this->config[ 'store_password' ];

        if ( isset($request[ 'verify_sign' ]) && isset($request[ 'verify_key' ]) ) {
            # NEW ARRAY DECLARED TO TAKE VALUE OF ALL POST

            $pre_define_key = explode(',', $request[ 'verify_key' ]);

            $new_data = [];
            if ( !empty($pre_define_key) ) {
                foreach ( $pre_define_key as $value ) {
                    if ( isset($request[ $value ]) ) {
                        $new_data[ $value ] = ( $request[ $value ] );
                    }
                }
            }
            # ADD MD5 OF STORE PASSWORD
            $new_data[ 'store_passwd' ] = md5($store_passwd);

            # SORT THE KEY AS BEFORE
            ksort($new_data);

            $hash_string = "";
            foreach ( $new_data as $key => $value ) {
                $hash_string .= $key . '=' . ( $value ) . '&';
            }
            $hash_string = rtrim($hash_string, '&');

            if ( md5($hash_string) == $request[ 'verify_sign' ] ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function statusFieldValidity( $request )
    {
        if ( $request[ 'status' ] == 'VALID' ) {
            return true;
        }

        return false;
    }

    private function sslCommerzValidationApiResponse( $request, $amount = null, $transactionId = null )
    {
        $query = [
            'val_id'       => $request[ 'val_id' ],
            'store_id'     => $this->config[ 'store_id' ],
            'store_passwd' => $this->config[ 'store_password' ]
        ];

        $response = $this->query($query)->get('validator/api/validationserverAPI.php');
        if ( !$response ) {
            return false;
        }

        $flag = true;
        $status = false;

        if ( $response->status == 'VALID' || $response->status == 'VALIDATED' ) {
            $status = true;
        }

        if (
            ( !is_null($amount) && $response->amount != $amount ) ||
            ( !is_null($transactionId) && $response->tran_id != $transactionId ) ||
            !$status
        ) {
            $flag = false;
        }

        return $flag;
    }

    private function routeOrUrl( $array )
    {
        if ( isset($array[ 'route' ]) && !is_null($array[ 'route' ]) ) {
            return route($array[ 'route' ]);
        } elseif ( isset($array[ 'url' ]) && !is_null($array[ 'url' ]) ) {
            return url($array[ 'url' ]);
        }

        return new \Exception();
    }

    private function preBuildParameter()
    {
        $this->params[ 'store_id' ] = $this->config[ 'store_id' ];
        $this->version();
        $this->redirectUrl();
    }

}
