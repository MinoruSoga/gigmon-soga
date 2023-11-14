import { useEffect, useState } from "react";
import axios from "axios";
import { Elements } from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import CheckoutForm from "../components/CheckoutForm";
import { Button, Modal } from "react-bootstrap";
import FullScreenLoader from "../components/FullScreenLoader";

export default function Payments(props) {
  const [stripePublicKey, setStripePublicKey] = useState(null);
  const [stripeReturnUrl, setStripeReturnUrl] = useState(null);
  const [currentPlan, setCurrentPlan] = useState({});
  const [allPlans, setAllPlans] = useState([]);
  const [selectedPlan, setSelectedPlan] = useState(null);
  const [clientSecret, setClientSecret] = useState(null);
  const [paymentMethod, setPaymentMethod] = useState(null);
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);
  const [editPaymentMethod, setEditPaymentMethod] = useState(false);
  const [showUnsubscribeModal, setShowUnsubscribeModal] = useState(false);
  const handleUnsubscribeClose = () => setShowUnsubscribeModal(false);
  const handleUnsubscribeShow = () => setShowUnsubscribeModal(true);
  const [showPauseServiceModal, setShowPauseServiceModal] = useState(false);
  const handlePauseServiceClose = () => setShowPauseServiceModal(false);
  const handlePauseServiceShow = () => setShowPauseServiceModal(true);
  const [showModal, setShowModal] = useState(false);
  const [selectedPlanId, setSelectedPlanId] = useState(null);
  const [stripePromise, setStripePromise] = useState(null);

  const openModal = (planId) => {
    setSelectedPlanId(planId);
    setShowModal(true);
  };

  const closeModal = () => {
    setSelectedPlanId(null);
    setShowModal(false);
  };

  if (props.role != 2) {
    return (
      <div id="error-page">
        <h1>Unauthorized!</h1>
      </div>
    );
  }

  useEffect(() => {
    //initial component load
    // Check to see if this is a redirect back from Checkout
    const query = new URLSearchParams(window.location.search);

    if (query.get("success")) {
      console.log("success load");
      setMessage({
        content: "登録が完了しました。",
        type: "success",
      });
      //load for 1 sec then refresh the page, to give webhook time to sync backend
      setLoading(true);
      setTimeout(() => {
        window.location.href = "/payments";
      }, 1000);
    } else {
      if (query.get("canceled")) {
        console.log("cancel load");
        setSuccess(false);
        setMessage({
          content: "登録が失敗しました。しばらくしてから再度お試しください。",
          type: "danger",
        });
        //load for 1 sec then refresh the page, to give webhook time to sync backend
        setLoading(true);
        setTimeout(() => {
          window.location.href = "/payments";
        }, 1000);
      }
      console.log("initial load");
      setLoading(true);
      axios
        .get("/api/payments")
        .then((response) => {
          const {
            currentPlan,
            allPlans,
            currentSubscription,
            paymentMethod,
            clientSecret,
            deleted_at,
          } = response.data;
          setCurrentPlan(currentPlan);
          setAllPlans(allPlans);
          setPaymentMethod(paymentMethod);
          setClientSecret(clientSecret);
          if (deleted_at !== null) {
            setMessage({
              content:
                "退会済みで今月末でアカウントデータが全て削除されます。データの復活が不可能です。", //All account data will be deleted at the end of this month. Data recovery is not possible.
              type: "warning",
            });
          } else if (!currentPlan) {
            setMessage({
              content:
                "アカウントが無効になっています。支払い方法を設定してプランを選択してください。", //Account is disabled. Set up your payment method and choose a plan.
              type: "warning",
            });
          }
          if (currentSubscription && !paymentMethod && currentPlan.id !== 1) {
            setSelectedPlan(currentPlan);
          }
          if (
            currentSubscription &&
            currentSubscription.status === "trialing" &&
            currentPlan.id === 1
          ) {
            setMessage({
              //You can try GiGMON for free until the end of this month. If you want to use after xxx, please select a plan below and register a payment method.
              content: `今月末までGiGMONを無料でお試し頂けます。
                        ${
                          currentSubscription?.trial_end
                            ? `${new Date(
                                currentSubscription?.trial_end * 1000
                              ).toLocaleString("ja-JP", {
                                year: "numeric",
                                month: "long",
                                day: "numeric",
                              })}
                                  以降も利用したい場合は以下でプランを選択して支払い方法を登録してください。`
                            : ""
                        }
                        `,
              type: "warning",
            });
          } else if (
            currentSubscription &&
            currentSubscription.status === "trialing" &&
            currentPlan.id !== 1
          ) {
            setMessage({
              //The free period is until the end of the month. The usage fee will be charged from xxxx and will be settled at the end of each month.
              content: `無料期間は${
                currentSubscription?.trial_end
                  ? `${new Date(
                      currentSubscription?.trial_end * 1000
                    ).toLocaleString("ja-JP", {
                      year: "numeric",
                      month: "long",
                      day: "numeric",
                    })}までです。その後は利用料が発生し、毎月末に決済が行われます。`
                  : ""
              }
                        `,
              type: "warning",
            });
          }
          setLoading(false);
        })
        .catch((error) => {
          console.error("API request error:", error);
        });

      axios.get("/env").then((response) => {
        setStripePublicKey(response.data.MIX_STRIPE_PUBLIC_KEY);
        setStripePromise(loadStripe(response.data.MIX_STRIPE_PUBLIC_KEY));
        setStripeReturnUrl(response.data.MIX_STRIPE_RETURN_URL);
      });
    }
  }, []);

  const cancelPaymentMethod = () => {
    //todo call this when back button is pressed.
    //api needs to figure out if they need to set it back to bank-transfer or remove current plan
    setLoading(true);
    axios
      .post("/api/payments/resetPaymentMethod")
      .then((response) => {
        //load for 1 sec then refresh the page, to give webhook time to sync backend
        setTimeout(() => {
          window.location.href = "/payments";
        }, 1000);
      })
      .catch((error) => {
        console.error("API request error:", error);
      });
  };

  const updatePaymentMethod = () => {
    setLoading(true);
    axios
      .post("/api/payments/createSetupIntent")
      .then((response) => {
        const { clientSecret } = response.data;
        setClientSecret(clientSecret);
        setLoading(false);
      })
      .catch((error) => {
        console.error("API request error:", error);
        setMessage({
          content: "問題が起こりました。しばらくしてから再度お試しください。",
          type: "danger",
        });
      });
    setEditPaymentMethod(true);
  };

  const signUpForPlan = (plan, method) => {
    setLoading(true);
    const data = {
      planId: plan.id,
      paymentMethod: method,
      companyPaymentMethod: method,
    };

    if (!paymentMethod) {
      setSelectedPlan(plan);
    }
    axios
      .post("/api/payments/createSubscription", data)
      .then((response) => {
        //if its a bank transfer, reload page
        //if cc had previously already been set up, reload page
        if (paymentMethod || method === "bank-transfer") {
          //load for 1 sec then refresh the page, to give webhook time to sync backend
          setTimeout(() => {
            window.location.href = "/payments";
          }, 1000);
          return;
        }
        const { clientSecret } = response.data;
        setClientSecret(clientSecret);
        setLoading(false);
      })
      .catch((error) => {
        console.error("API request error:", error);
      });
  };

  const getCheckoutType = (planId, currentPlanId) => {
    if (planId == 5) return "お問合せ"; //contact
    if (!currentPlanId || currentPlanId === 1) {
      return "登録"; //register
    }
    if (currentPlanId === planId) {
      return "利用中"; //in use
    }
    if (currentPlanId > planId) {
      return "変更"; //change
    }
    if (currentPlanId < planId) {
      return "変更"; //change
    }
  };

  const handleUnsubscribeConfirmed = (permanentlyDelete = false) => {
    setLoading(true);
    axios
      .post("/api/payments/cancelSubscription", {
        permanentlyDelete,
      })
      .then((response) => {
        //load for 1 sec then refresh the page, to give webhook time to sync backend
        setTimeout(() => {
          location.reload();
        }, 1000);
      })
      .catch((error) => {
        console.error("API request error:", error);
      });
  };

  const ProductDisplay = () => (
    <section>
      {message && message.content && (
        <div
          className={`alert alert-${message?.type || "warning"}`}
          role="alert"
        >
          {message.content}
        </div>
      )}

      {loading ? (
        <FullScreenLoader isLoading={loading} />
      ) : (
        <>
          <div className="container">
            {currentPlan && paymentMethod && (
              <div className="row justify-content-center mx-1 mb-3">
                <div className="card col-md-9 mt-3">
                  <div className="card-body">
                    <h5 className="card-title">
                      <strong>利用中のプラン： {currentPlan?.name}</strong>
                    </h5>
                    <h5 className="card-title">
                      {paymentMethod?.card?.last4
                        ? `支払方法：${paymentMethod?.card?.brand.toUpperCase()}カード （**** **** **** ${
                            paymentMethod?.card?.last4
                          }）`
                        : ""}
                      {paymentMethod === "bank-transfer"
                        ? `支払方法：銀行振込`
                        : ""}
                      {paymentMethod && paymentMethod !== "bank-transfer" ? (
                        <button
                          onClick={() => {
                            updatePaymentMethod();
                          }}
                          className="btn btn-naked text-primary"
                        >
                          クレジットカードを変更 →
                        </button>
                      ) : null}
                    </h5>
                    <div>
                      <p>
                        {paymentMethod && paymentMethod == "bank-transfer"
                          ? "利用金額が月末に確定します。金額が確定次第メールにて請求書を送付します。"
                          : "利用金額が月末に確定します。金額が確定次第、クレジットカードから自動的に引き落とされ、請求書をメールにて送付します。"}
                      </p>
                    </div>
                    <div className="d-flex justify-content-between">
                      <button
                        type="button"
                        className="btn btn-primary"
                        onClick={openModal}
                      >
                        支払方法を変更
                      </button>
                      <div>
                        <button
                          type="button"
                          className="btn btn-warning"
                          onClick={handlePauseServiceShow}
                          style={{
                            marginRight: "10px",
                          }}
                        >
                          利用休止
                        </button>
                        <button
                          type="button"
                          className="btn btn-danger"
                          onClick={handleUnsubscribeShow}
                        >
                          退会
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            )}
            <Modal show={showUnsubscribeModal} onHide={handleUnsubscribeClose}>
              <Modal.Header closeButton>
                <Modal.Title>退会</Modal.Title>
              </Modal.Header>
              <Modal.Body>
                退会するとこのアカウントと関連している従業員のアカウントのデータが全て削除され、復活できなくなります。本当に退会しますか？
              </Modal.Body>
              <Modal.Footer>
                <Button variant="" onClick={handleUnsubscribeClose}>
                  キャンセル
                </Button>
                <Button
                  variant="primary"
                  onClick={() => {
                    handleUnsubscribeConfirmed(true);
                  }}
                >
                  退会する
                </Button>
              </Modal.Footer>
            </Modal>
            <Modal
              show={showPauseServiceModal}
              onHide={handlePauseServiceClose}
            >
              <Modal.Header closeButton>
                <Modal.Title>利用休止</Modal.Title>
              </Modal.Header>
              <Modal.Body>
                利用休止でアカウントが利用できなくなりますが、チャットデータなどは削除されません。再度支払い情報を設定すれば利用再開が可能です。
              </Modal.Body>
              <Modal.Footer>
                <Button variant="" onClick={handlePauseServiceClose}>
                  キャンセル
                </Button>
                <Button
                  variant="primary"
                  onClick={() => {
                    handleUnsubscribeConfirmed(false);
                  }}
                >
                  利用休止
                </Button>
              </Modal.Footer>
            </Modal>
            <Modal show={showModal} onHide={closeModal}>
              <Modal.Header closeButton>
                <Modal.Title>支払方法を変更</Modal.Title>
              </Modal.Header>
              {paymentMethod == "bank-transfer" ? (
                <Modal.Body>
                  支払方法をクレジットカードに変更しますか？
                </Modal.Body>
              ) : (
                <Modal.Body>支払方法を銀行振込に変更しますか？</Modal.Body>
              )}
              <Modal.Footer>
                <Button
                  onClick={() => {
                    paymentMethod == "bank-transfer"
                      ? signUpForPlan(currentPlan, "credit-card")
                      : signUpForPlan(currentPlan, "bank-transfer");
                    closeModal();
                  }}
                >
                  変更
                </Button>
                <Button variant="secondary" onClick={closeModal}>
                  戻る
                </Button>
              </Modal.Footer>
            </Modal>
            <div className="row d-flex justify-content-center align-items-center">
              {!selectedPlan && !editPaymentMethod && (
                <>
                  {allPlans.map((plan) => {
                    if (plan.id === 0) {
                      return;
                    }
                    const type = getCheckoutType(plan.id, currentPlan?.id);
                    return (
                      <div
                        key={`plan-${plan.id}-wrapper`}
                        className="col-3 mt-3"
                      >
                        <Plan
                          plan={plan}
                          type={type}
                          currentPlanId={currentPlan?.id}
                          signUpForPlan={signUpForPlan}
                          showModal={showModal && selectedPlanId === plan.id}
                          openModal={() => openModal(plan.id)}
                          closeModal={closeModal}
                        />
                      </div>
                    );
                  })}
                </>
              )}
            </div>
            {selectedPlan && (
              <div className="row justify-content-center">
                <div className="col-md-8">
                  <Plan
                    selected={true}
                    plan={selectedPlan}
                    currentPlanId={currentPlan?.id}
                    signUpForPlan={signUpForPlan}
                    showModal={showModal && selectedPlanId === plan.id}
                    openModal={() => openModal(plan.id)}
                    closeModal={closeModal}
                  />
                </div>
              </div>
            )}
          </div>
          {clientSecret &&
            ((!paymentMethod && selectedPlan) ||
              (paymentMethod && editPaymentMethod)) && (
              <Elements
                stripe={stripePromise}
                options={{ clientSecret, locale: "ja" }}
              >
                <CheckoutForm stripeReturnUrl={stripeReturnUrl} />
              </Elements>
            )}
        </>
      )}
    </section>
  );

  const Plan = ({
    plan,
    type,
    currentPlanId,
    selected = false,
    signUpForPlan,
    showModal,
    openModal,
    closeModal,
  }) => {
    const handlePaymentOption = (plan, method) => {
      signUpForPlan(plan, method);
      closeModal();
    };

    return (
      <div key={plan.id} className={`card ${!selected && "mx-1"} mt-3`}>
        <div className="card-body">
          {selected && (
            <button
              onClick={() => {
                setSelectedPlan(null);
                setLoading(true);
                cancelPaymentMethod();
              }}
              className="btn btn-naked text-primary"
            >
              ← 戻る
            </button>
          )}
          {selected && (
            <h2 className="card-title">選択されているプラン：{plan.name}</h2>
          )}
          {!selected && <h5 className="card-title">{plan.name}</h5>}
          {plan.id === 2 && <h3 className="card-text">¥7,500</h3>}
          {plan.id === 3 && <h3 className="card-text">¥24,000</h3>}
          {plan.id === 4 && <h3 className="card-text">¥80,000</h3>}
          {plan.id === 5 && <h3 className="card-text">要相談</h3>}
          <p className="card-text">月額（固定）</p>
          <h3 className="card-text">+ ¥30</h3>
          <p className="card-text">/1000文字（利用分）</p>
          {!selected && type && (
            <Button
              className="btn btn-primary"
              disabled={loading || plan.id === currentPlanId}
              onClick={() => {
                if (plan.id === 5) {
                  window.location.href = "/contact";
                  return;
                }
                if (!paymentMethod) {
                  openModal();
                } else {
                  const method =
                    paymentMethod === "bank-transfer"
                      ? "bank-transfer"
                      : "credit-card";
                  signUpForPlan(plan, method);
                }
              }}
            >
              {type}
            </Button>
          )}

          <Modal show={showModal} onHide={closeModal}>
            <Modal.Header closeButton>
              <Modal.Title>支払方法を選択</Modal.Title>
            </Modal.Header>
            <Modal.Body>
              支払方法を選択してください。毎月料金が確定したら請求書がメールで送られます。
            </Modal.Body>
            <Modal.Footer className="justify-content-between">
              <div>
                <Button
                  style={{ marginRight: "10px" }}
                  onClick={() => handlePaymentOption(plan, "credit-card")}
                >
                  クレジットカード
                </Button>
                <Button
                  onClick={() => handlePaymentOption(plan, "bank-transfer")}
                >
                  銀行振込
                </Button>
              </div>
              <Button variant="secondary" onClick={closeModal}>
                戻る
              </Button>
            </Modal.Footer>
          </Modal>
        </div>
      </div>
    );
  };

  return <ProductDisplay />;
}
