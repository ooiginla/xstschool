<?php

function sr($message = "", $data = [], $code = 200)
{
    return response()->json([
        'status' => false,
        'message' => $message,
        'data' => array_merge([], $data)
    ], $code)->throwResponse();
}

function getWalletTitleFromType($type)
{
    switch ($type) {
            // case "wallet_balance":
            //     return "Wallet Balance";
            //     break;
        case "repayment_balance":
            return "Loan Funds (Repayments)";
            break;
        case "disbursement_balance":
            return "Loan Funds (Disbursement)";
            break;
            // case "profit_balance":
            //     return "Loan Funds (Profit)";
            //     break;

        default:
            return "Wallet Balance";
            break;
    }
}

if (! function_exists('generateRef')) {
    function generateRef($type, $reference=null)
    {
        $prefix = "GEN";

        switch($type){
            case "repayment":
                $prefix = "RPY";
                break;
            
            case "loan":
                $prefix = "LOA";
                break;
    
            case "disbursement":
                $prefix = "DIS";
                break;

            case "fee":
                $prefix = "FEE";
                break;

            case "kyc":
                $prefix = "KYC";
                break;

            case "creditscore":
                $prefix = "CRC";
                break;

            case "transfer":
                $prefix = "TXF";
                break;

            case "payout":
                    $prefix = "PAY";
                    break;

            case "movement":
                $prefix = "MOV";
                break;

            case "payin":
                $prefix = "PIN";
                break;
            
            default:
                $prefix = "GEN";
                break;
        }

        if(!empty($reference)){
            return "OYSTR_".$prefix.substr($reference, 9);
        }

        return  "OYSTR_".$prefix ."_". time() . strtoupper("_" . Str::random(4));
    }

    
}

 