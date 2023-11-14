import { Button, Modal } from "react-bootstrap";

const CustomModal = (props) => {
  const {
    showModal,
    closeModal,
    title,
    body,
    buttonCta,
    buttonOnClick,
    secondaryCta,
    secondaryOnClick,
  } = props;

  return (
    <Modal show={showModal} onHide={closeModal}>
      <Modal.Header closeButton>
        {title && <Modal.Title>{title}</Modal.Title>}
      </Modal.Header>
      {body && <Modal.Body>{body}</Modal.Body>}
      <Modal.Footer>
        {secondaryCta && secondaryOnClick ? (
          <Button variant="secondary" onClick={secondaryOnClick}>
            {secondaryCta}
          </Button>
        ) : null}
        <Button onClick={buttonOnClick}>{buttonCta}</Button>
      </Modal.Footer>
    </Modal>
  );
};

export default CustomModal;
