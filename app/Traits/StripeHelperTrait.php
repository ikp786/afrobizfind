<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\CardException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

trait StripeHelperTrait
{

    public function makePaymentMethod($stripe)
    {
        try {
            $conf = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number' => '4000003560000008',
                    'exp_month' => 9,
                    'exp_year' => 2025,
                    'cvc' => '314',
                ],
            ]);
            return $conf;
        } catch (CardException $th) {
            return $th->getMessage();
        }
    }

    public function attachPaymentMethodToCustomer($stripe, $payment_method_id, $customer_id)
    {
        // $payment_methods = ($this->checkCustomerPaymentMethods($stripe, $customer_id));

        // return $payment_methods;

        // if (isset($payment_methods->data) && typeOf($payment_methods->data) == 'array') {
        //     // foreach(){

        //     // }
        // }

        return $stripe->paymentMethods->attach(
            $payment_method_id,
            ['customer' => $customer_id]
        );
    }

    public function checkCustomerPaymentMethods($stripe, $customer_id)
    {
        return $stripe->customers->allPaymentMethods(
            $customer_id,
            ['type' => 'card']
        );
    }

    public function makeConnectedAccount($stripe, $user, $request)
    {

        try {

            if ($user->account_id != null) {
                // $acc = $stripe->accounts->retrieve(
                //     $user->account_id,
                //     []
                // );

                $acc = $stripe->accounts->update(
                    $user->account_id,
                    [
                        'email' => $user->email,
                        // 'country' => $request->country,
                        'business_type' => 'individual',
                        'capabilities' => [
                            'card_payments' => ['requested' => true],
                            'transfers' => ['requested' => true],
                        ],
                        'tos_acceptance' => [
                            'date' => 1385798567,
                            'ip' => '49.36.85.241'
                        ],
                        'external_account' => [
                            'object' => 'bank_account',
                            'country' => $request->country,
                            'currency' => $request->currency,
                            'account_holder_name' => $user->name,
                            'account_holder_type' => 'individual',
                            'account_number' => $request->account_number,
                            'routing_number' => $request->routing_number
                        ],
                        'business_profile' => [
                            'name' => $user->first_name . ' - ' . $user->email,
                            "mcc" => $request->mcc,
                            'product_description' => 'product_description',
                            'support_address' => [
                                'city' => 'ABERDEEN',
                                'country' => $request->country,
                                'line1' => 'asfafafaf',
                                'line2' => 'asfafafaf',
                                'postal_code' => 'AB10 6DN',
                                'state' => 'Washington',
                            ]
                        ],
                        'individual' => [
                            'address' => [
                                'city' => 'ABERDEEN',
                                'line1' => 'asfafafaf',
                                'postal_code' => 'AB10 6DN',
                            ],
                            'dob' => [
                                'day' => 1,
                                'month' => 1,
                                'year' => 2002,
                            ],
                            'email' => $user->email,
                            'first_name' => $user->first_name,
                            'last_name' => $user->surname,
                            'phone' => $user->mobile_number,
                        ],
                        'settings' => [
                            'payouts' => [
                                'statement_descriptor' => 'Afro biz find charge'
                            ]
                        ]
                    ]
                );
            } else {
                $acc = $stripe->accounts->create([
                    'type' => 'custom',
                    'country' => $request->country,
                    'email' => $user->email,
                    'business_type' => 'individual',
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'tos_acceptance' => [
                        'date' => 1385798567,
                        'ip' => '49.36.85.241'
                    ],
                    'external_account' => [
                        'object' => 'bank_account',
                        'country' => $request->country,
                        'currency' => $request->currency,
                        'account_holder_name' => $user->name,
                        'account_holder_type' => 'individual',
                        'account_number' => $request->account_number,
                        'routing_number' => $request->routing_number
                    ],
                    'business_profile' => [
                        'name' => $user->first_name . ' - ' . $user->email,
                        "mcc" => $request->mcc,
                        'product_description' => 'product_description',
                        'support_address' => [
                            'city' => 'ABERDEEN',
                            'country' => $request->country,
                            'line1' => 'asfafafaf',
                            'line2' => 'asfafafaf',
                            'postal_code' => 'AB10 6DN',
                            'state' => 'Washington',
                        ]
                    ],
                    'individual' => [
                        'address' => [
                            'city' => 'ABERDEEN',
                            'line1' => 'asfafafaf',
                            'postal_code' => 'AB10 6DN',
                        ],
                        'dob' => [
                            'day' => 1,
                            'month' => 1,
                            'year' => 2002,
                        ],
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->surname,
                        'phone' => $user->mobile_number,
                    ],
                    'settings' => [
                        'payouts' => [
                            'statement_descriptor' => 'Afro biz find charge'
                        ]
                    ]
                ]);
            }

            if ($acc) {
                $user->account_id = $acc->id;
                $user->save();
            }
            return ($acc);
        } catch (\Throwable $th) {
            return response()->json([
                'result' => 0,
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function makePaymentIntent($user, $request, int $payOutPercent, $stripe)
    {
        // $intentArray = [
        //     'amount' => $request->amount * 100,
        //     'currency' => $request->currency,
        //     'transfer_data' => [
        //         'amount' => $request->amount * $payOutPercent,
        //         'destination' => $user->account_id,
        //     ],
        // ];

        $response = $stripe->transfers->create([
            'amount' => $request->amount * 100,
            'currency' => $request->currency,
            'destination' => $user->account_id,
            'transfer_group' => 'ORDER_95',
        ]);

        // $response = \Stripe\Charge::create([
        //     'amount' => $request->amount * 100,
        //     'currency' => $request->currency,
        //     'customer' => 'cus_MLne6iK1yNJ1Oz',
        //     'transfer_group' => date('y-m-d_h:i:s'),
        //     'transfer_data' => [
        //         'destination' => $user->account_id, // destination id
        //         'amount' => $request->amount * $payOutPercent
        //     ]
        // ]);

        // $response = PaymentIntent::create($intentArray);
        return $response;
    }
}
