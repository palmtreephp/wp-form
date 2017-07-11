<?php

namespace Palmtree\WordPress\Form;

use Palmtree\Form\Form;
use Palmtree\Http\RemoteUser;

abstract class AbstractForm
{
    /** @var Form $form */
    protected $form;
    public $args = [];
    protected $errors = [];
    protected $successMessage = 'Thank you for your message.';
    protected $errorMessage = 'Oops! Something went wrong there, please check the form for errors.';
    protected $abortMessage = 'Oops! An unknown error occurred. Please try again later.';
    protected $logger;

    public function __construct(FormLogger $logger = null)
    {
        $this->logger = $logger;
        add_action('wp_loaded', [$this, 'parseRequest']);
    }

    public function parseRequest()
    {
        $form = $this->getForm();
        $form->handleRequest();

        if (!$form->isSubmitted()) {
            return;
        }

        $isAjax = $form->isAjax() && Form::isAjaxRequest();

        if ($form->isValid()) {
            $redirectField = $form->getField('redirect_to');
            $redirectTo    = ($redirectField) ? $redirectField->getData() : false;

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

    abstract protected function createForm();

    protected function onSuccess()
    {
        $this->logger->log($this->getLogBody());
    }

    protected function getLogBody()
    {
        $message = '';

        $message .= "----- START OF MESSAGE -----\n\n";

        foreach ($this->form->getFields(['userInput' => true]) as $field) {
            $message .= $field->getLabel() . ': ';

            if ($field->getTag() === 'textarea') {
                $message .= "\n";
            }

            $message .= $field->getData() . "\n\n";
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

    /**
     * @param mixed $successMessage
     *
     * @return AbstractForm
     */
    public function setSuccessMessage($successMessage)
    {
        $this->successMessage = $successMessage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            $this->form = $this->createForm();
        }

        return $this->form;
    }
}
