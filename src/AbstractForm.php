<?php declare(strict_types=1);

namespace Palmtree\WordPress\Form;

use Palmtree\Form\Form;
use Palmtree\Http\RemoteUser;

abstract class AbstractForm
{
    /** @var Form|null */
    protected $form;
    /** @var array */
    public $args = [];
    /** @var array */
    protected $errors = [];
    /** @var string */
    protected $successMessage = 'Thank you for your message.';
    /** @var string */
    protected $errorMessage = 'Oops! Something went wrong there, please check the form for errors.';
    /** @var string */
    protected $abortMessage = 'Oops! An unknown error occurred. Please try again later.';
    /** @var FormLogger|null */
    protected $logger;

    public function __construct(?FormLogger $logger = null)
    {
        $this->logger = $logger;
        add_action('wp_loaded', [$this, 'parseRequest']);
    }

    public function parseRequest(): void
    {
        $form = $this->getForm();
        $form->handleRequest();

        if (!$form->isSubmitted()) {
            return;
        }

        $isAjax = $form->isAjax() && Form::isAjaxRequest();

        if ($form->isValid()) {
            $redirectField = $form->get('redirect_to');
            $redirectTo = ($redirectField) ? $redirectField->getData() : false;

            try {
                $this->onSuccess();
            } catch (\Exception $e) {
                if ($isAjax) {
                    wp_send_json_error(['message' => $this->abortMessage]);
                } else {
                    $this->errors[] = $this->abortMessage;
                }
            }

            if ($isAjax) {
                wp_send_json_success(['message' => $this->successMessage]);
            } elseif ($redirectTo) {
                wp_safe_redirect($redirectTo);
                exit;
            }
        } else {
            $this->errors = $form->getErrors();

            $this->onFailure();

            if ($isAjax) {
                wp_send_json_error(['message' => $this->errorMessage, 'errors' => $this->errors]);
            }
        }
    }

    abstract protected function createForm(): Form;

    protected function onSuccess()
    {
        $this->logger->log($this->getLogBody());
    }

    protected function getLogBody(): string
    {
        $message = '';

        $message .= "----- START OF MESSAGE -----\n\n";

        foreach ($this->form->all() as $field) {
            if ($field->isUserInput()) {
                $message .= $field->getLabel() . ': ';

                if ($field->getTag() === 'textarea') {
                    $message .= "\n";
                }

                $message .= $field->getData() . "\n\n";
            }
        }

        $message .= "----- END OF MESSAGE -----\n\n";

        $user = new RemoteUser();

        $message .= 'IP Address: ' . $user->getIpAddress() . "\n";
        $message .= 'User Agent: ' . $user->getUserAgent() . "\n";

        return $message;
    }

    protected function onFailure()
    {
    }

    public function setSuccessMessage(string $successMessage): self
    {
        $this->successMessage = $successMessage;

        return $this;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    public function getForm(): Form
    {
        if (!isset($this->form)) {
            $this->form = $this->createForm();
        }

        return $this->form;
    }
}
