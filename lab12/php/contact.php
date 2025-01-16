<?php
class Contact {
    // Function to display the contact form
    function PokazKontakt() {
        return '
        <div class="page-section-idp-6">
            <h2>Skontaktuj się z nami</h2>
            <div class="content-block">
                <form method="post" name="ContactForm" action="' . $_SERVER['REQUEST_URI'] . '">
                    <div class="form-group">
                        <label for="email">Twój email</label>
                        <input type="email" id="email" name="email" required />
                    </div>
                    <div class="form-group">
                        <label for="title">Temat wiadomości</label>
                        <input type="text" id="title" name="title" required />
                    </div>
                    <div class="form-group">
                        <label for="message">Treść wiadomości</label>
                        <textarea id="message" name="message" rows="4" required></textarea>
                    </div>
                    <input type="submit" class="submit-button" value="Wyślij wiadomość" />
                </form>
            </div>
            <p class="info-text">Prosimy o podanie aktualnego adresu email, na który odeślemy odpowiedź.</p>
        </div>'; // Return the HTML for the contact form
    }

    // Function to send a contact email
    function WyslijMailKontakt($odbiorca) {
        // Check if required fields are filled
        if (empty($_POST['temat']) || empty($_POST['tresc']) || empty($_POST['email'])) {
            echo ['nie_wypelniles_pola']; // Error message for empty fields
            echo $this->PokazKontakt(); // Show contact form again
        } else {
            // Prepare email data
            $mail = [
                'sender' => '169394@student.uwm.edu.pl',
                'subject' => $_POST['temat'],
                'body' => $_POST['tresc'],
                'recipient' => $_POST['email']
            ];

            // Set email headers
            $header = "From: Formularz kontaktowy <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n";

            mail($mail['recipient'], $mail['subject'], $mail['body'], $header); // Send email
            echo '[wiadomosc_wyslana]'; // Success message
        }
    }

    // Function to remind the user of their password
    function PrzypomnijHaslo($odbiorca) {
        // Check if email field is empty
        if (empty($_POST['email_recov'])) {
            return '
            <div class="logowanie">
                <h3 class="heading">Przypomnij Hasło:</h3>
                <form method="post" name="PasswordRecoveryForm" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="logowanie">
                        <tr>
                            <td class="log4_t">Email:</td>
                            <td><input type="email" name="email_recov" class="logowanie" required /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="przypomnij_submit" class="logowanie" value="Wyślij" /></td>
                        </tr>
                    </table>
                </form>
            </div>'; // Return the password recovery form
        } else {
            // Prepare email data for password reminder
            $mail = [
                'sender' => '169394@student.uwm.edu.pl',
                'subject' => "Przypomnienie hasła",
                'body' => "Twoje hasło do panelu administracyjnego to: " . ADMIN_PASSWORD,
                'recipient' => $_POST['email_recov']
            ];

            // Set email headers
            $header = "From: Przypomnienie hasła <" . $mail['sender'] . ">\n";
            $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n";

            // Send the email and return success or error message
            if (mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
                return '<div class="logowanie"><h3 class="heading">Hasło zostało wysłane na podany adres email.</h3></div>';
            } else {
                return '<div class="logowanie"><h3 class="heading">Wystąpił błąd podczas wysyłania emaila.</h3></div>';
            }
        }
    }
}
?>