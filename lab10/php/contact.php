<?php
        class Contact {
        // Method to display the contact form

        function PokazKontakt() { // Zwracanie kodu HTML dla formularza kontaktowego
            return '
            <div class="logowanie">
                <h3 class="heading">Panel CMS:</h3>
                <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="logowanie">
                        <tr><td class="log4_t">[login]</td><td><input type="text" name="login" class="logowanie" required /></td></tr>
                        <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" required /></td></tr>
                        <tr><td></td><td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj" /></td></tr>
                    </table>
                </form>
            </div>
        
            <div class="kontakt">
                <h3 class="heading">Formularz Kontaktowy:</h3>
                <form method="post" name="ContactForm" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="kontakt">
                        <tr><td class="kontakt_t">[email]</td><td><input type="email" name="email" class="kontakt" required /></td></tr>
                        <tr><td class="kontakt_t">[tytul]</td><td><input type="text" name="title" class="kontakt" required /></td></tr>
                        <tr><td class="kontakt_t">[wiadomosc]</td><td><textarea name="message" class="kontakt" rows="4" cols="50" required></textarea></td></tr>
                        <tr><td></td><td><input type="submit" name="x2_submit" class="kontakt" value="wyślij" /></td></tr>
                    </table>
                </form>
            </div>';
        }
        

        // Metoda do wysłania wiadomości e-mail z formularza kontaktowego
        function WyslijMailKontakt($odbiorca) {
            // Sprawdzenie, czy wszystkie pola formularza są wypełnione
            if (empty($_POST['temat']) ||
                empty($_POST['tresc']) || 
                empty($_POST['email'])) { 
                echo ['nie_wypelniles_pola'];
                echo $this->PokazKontakt(); // ponowne wypelnienie formularza
            }
            else {
                // Przygotowanie danych wiadomości e-mail
                $mail['subject'] = $_POST['temat'];
                $mail['body'] = $_POST['tresc'];
                $mail['sender'] = $_POST['email'];
                $mail['recipient'] = $odbiorca; // czyli my jestesmy odbiorca, jezeli tworzymy formularz kontaktowy

                // Ustawienia nagłówków wiadomości e-mail
                $header = "From: Formularz kontaktowy <".$mail['sender'].">\n";
                $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit\n";
                $header .= "X-Sender: ".$mail['sender'].">\n";
                $header .= "X-Mailer: PRapWWW mail 1.2\n";
                $header .= "X-Priority: 3\n";
                $header .= "Return-Path: <".$mail['sender'].">\n";

                // Wysłanie wiadomości e-mail
                mail($mail['recipient'], 
                    $mail['subject'],  
                    $mail['body'], $header);
                echo '[wiadomosc_wyslana]';
            }
        }

        // Method to display the password recovery form
        function PrzypomnijHaslo($odbiorca) {
            if (empty($_POST['email_recov'])) { // Check if the email field for password recovery is empty
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
                </div>';
            } else {
                // Prepare the email data for password recovery
                $mail['sender'] = '169394@student.uwm.edu.pl';
                $mail['subject'] = "Przypomnienie hasła";
                $mail['body'] = "Twoje hasło do panelu administracyjnego to: " . ADMIN_PASSWORD;
                $mail['recipient'] = $_POST['email_recov'];

                // Email headers
                $header = "From: Przypomnienie hasła <" . $mail['sender'] . ">\n";
                $header .= "MIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\n";
                $header .= "X-Sender: <" . $mail['sender'] . ">\n";
                $header .= "X-Priority: 3\n";

                // Send the email and check if the operation was successful
                if (mail($mail['recipient'], $mail['subject'], $mail['body'], $header)) {
                    return '<div class="logowanie"><h3 class="heading">Hasło zostało wysłane na podany adres email.</h3></div>';
                } else {
                    return '<div class="logowanie"><h3 class="heading">Wystąpił błąd podczas wysyłania emaila.</h3></div>';
                }
            }
        }

        // Method to display the email field for password recovery
        function PokazKontaktHaslo() {
            // Zwracanie kodu HTML dla pola e-mail dla odzyskiwania hasła
            return ' 
            <div class="form_passrecov">
                <form method="post" action="' . $_SERVER['REQUEST_URI'] . '">
                    <table class="form_passrecov">
                        <tr>
                            <td>Email:</td>
                            <td><input type="text" name="email_recov" required style="width: 100%;" /></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" value="Send" class="submit-button" /></td>
                        </tr>
                    </table>
                </form>
                <div class="buttons2">
                    <a class="contact-button" href="?idp=kontakt">Contact</a>
                </div>
            </div>';
        }
    }
?>
