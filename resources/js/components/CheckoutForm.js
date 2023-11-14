import { useState } from "react";
import {
  useStripe,
  useElements,
  PaymentElement,
} from "@stripe/react-stripe-js";

const CheckoutForm = ({ stripeReturnUrl }) => {
  const stripe = useStripe();
  const elements = useElements();
  const [loading, setLoading] = useState(false);
  const handleSubmit = async (event) => {
    event.preventDefault();
    setLoading(true);
    if (!stripe || !elements) {
      return;
    }
    let result;
    try {
      result = await stripe.confirmSetup({
        elements,
        confirmParams: {
          return_url: stripeReturnUrl + "payments?success=success",
        },
      });
    } catch (error) {
      result = await stripe.confirmPayment({
        elements,
        confirmParams: {
          return_url: stripeReturnUrl + "payments?success=success",
        },
      });
    }

    if (result.error) {
      setLoading(false);
      console.log(result.error.message);
    }
  };

  return (
    <div className="container mt-3">
      <div className="row justify-content-center">
        <div className="col-md-8">
          <div className="card" style={{ padding: "20px" }}>
            <form onSubmit={handleSubmit}>
              <PaymentElement />
              <button
                className="btn btn-primary mt-2"
                disabled={!stripe || loading}
              >
                {!loading ? "登録" : "登録中..."}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CheckoutForm;
