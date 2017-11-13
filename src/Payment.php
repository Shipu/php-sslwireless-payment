<?php

namespace Shipu\SslWPayment;

use Shipu\SslWPayment\Exceptions\RouteOrUrlNotFound;

class Payment extends AbstractApi
{
    protected $config;
    protected $params = [];

    /**
     * Payment constructor.
     *
     * @param $config
     */
    public function __construct( $config )
    {
        $this->config = $config;
        $this->preBuildParameter();
        parent::__construct();
    }

    /**
     * Getting Payment Url
     *
     * @return string
     */
    public function paymentUrl()
    {
        return $this->client->baseUrl . 'gwprocess/v3/process.php';
    }

    /**
     * Set Customer Info
     *
     * @param array $info
     *
     * @return $this
     */
    public function customer( $info = [] )
    {
        $this->params = array_merge($this->params, $info);

        return $this;
    }

    /**
     * Set Transaction Id
     *
     * @param null $transaction
     *
     * @return $this
     */
    public function transactionId( $transaction = null )
    {
        if(is_null($transaction)) {
            $transaction = $this->generateTransaction();
        }

        $this->params['tran_id'] = $transaction;

        return $this;
    }

    /**
     * Set Payment Amount
     *
     * @param $amount
     *
     * @return $this
     */
    public function amount( $amount )
    {
        $this->params[ 'total_amount' ] = $amount;

        return $this;
    }

    /**
     * Getting Redirect Url
     *
     * @return $this
     */
    public function redirectUrl()
    {
        $redirectUrl = $this->config[ 'redirect_url' ];
        $this->params[ 'success_url' ] = $this->routeOrUrl($redirectUrl[ 'success' ]);
        $this->params[ 'fail_url' ] = $this->routeOrUrl($redirectUrl[ 'fail' ]);
        $this->params[ 'cancel_url' ] = $this->routeOrUrl($redirectUrl[ 'cancel' ]);

        return $this;
    }

    /**
     * Set Version
     *
     * @param string $version
     *
     * @return $this
     */
    public function version( $version = '3.00' )
    {
        $this->params[ 'version' ] = $version;

        return $this;
    }

    /**
     * Generate Transactions
     *
     * @return $this
     */
    public function generateTransaction()
    {
        $this->params[ 'tran_id' ] = uniqid(true) . microtime(true);

        return $this;
    }

    /**
     * Getting Hidden Input Value
     * @return string
     */
    public function hiddenValue()
    {
        if(!isset($this->params['tran_id'])) {
            $this->params['tran_id'] = $this->generateTransaction();
        }

        $formString = '';
        foreach ( $this->params as $name => $value ) {
            $formString .= "<input type=\"hidden\" name=\"" . $name . "\" value=\"" . $value . "\" />";
        }

        return $formString;
    }

    /**
     * Checking Valid Response Request with amount and transactionId
     *
     * @param $request
     * @param null $amount
     * @param null $transactionId
     *
     * @return bool
     */
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

    /**
     * Checking Valid Amount in response
     *
     * @param $request
     * @param $amount
     *
     * @return bool
     */
    public function validAmount( $request, $amount )
    {
        return $this->sslCommerzValidationApiResponse($request, $amount);
    }

    /**
     * Checking valid transaction id in response
     *
     * @param $request
     * @param $transactionId
     *
     * @return bool
     */
    public function validTransactionId( $request, $transactionId )
    {
        return $this->sslCommerzValidationApiResponse($request, null, $transactionId);
    }


    /**
     * Checking Hash Versify
     *
     * @param $request
     *
     * @return bool
     */
    private function hashVerify( $request )
    {
        $storePassword = $this->config[ 'store_password' ];

        if ( isset($request[ 'verify_sign' ]) && isset($request[ 'verify_key' ]) ) {

            $preDefineKey = explode(',', $request[ 'verify_key' ]);

            $newData = [];
            if ( !empty($preDefineKey) ) {
                foreach ( $preDefineKey as $value ) {
                    if ( isset($request[ $value ]) ) {
                        $newData[ $value ] = ( $request[ $value ] );
                    }
                }
            }
            $newData[ 'store_passwd' ] = md5($storePassword);

            ksort($newData);

            $hashString = "";
            foreach ( $newData as $key => $value ) {
                $hashString .= $key . '=' . ( $value ) . '&';
            }
            $hashString = rtrim($hashString, '&');

            if ( md5($hashString) == $request[ 'verify_sign' ] ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Checking Valid Status
     *
     * @param $request
     *
     * @return bool
     */
    private function statusFieldValidity( $request )
    {
        if ( $request[ 'status' ] == 'VALID' ) {
            return true;
        }

        return false;
    }

    /**
     * Checking Valid Response
     *
     * @param $request
     * @param null $amount
     * @param null $transactionId
     *
     * @return bool
     */
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

    /**
     * Getting Route or url
     *
     * @param $array
     *
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     * @throws RouteOrUrlNotFound
     */
    private function routeOrUrl( $array )
    {
        if ( isset($array[ 'route' ]) && !is_null($array[ 'route' ]) && $array[ 'route' ] != '' ) {
            return route($array[ 'route' ]);
        } elseif ( isset($array[ 'url' ]) && !is_null($array[ 'url' ]) && $array[ 'url' ] != '' ) {
            return url($array[ 'url' ]);
        }

        throw new RouteOrUrlNotFound();
    }

    /**
     * Pre build Parameter for post request
     */
    private function preBuildParameter()
    {
        $this->params[ 'store_id' ] = $this->config[ 'store_id' ];
        $this->version();
        $this->redirectUrl();
    }

}
