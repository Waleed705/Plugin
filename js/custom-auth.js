jQuery(document).ready(function($) {
    $("#signup").click(function(e) {
        e.preventDefault();
        let fname = $("#name").val();
        let email = $("#email").val();
        let mpassword = $("#current-password").val();
        let checkbox = $("#checkbox");
    
        let nameerror = $("#name-error");
        let emailerror = $("#email-error");
        let passworderror = $("#password-error");
        let checkerror = $("#check-error");
        
        $("#name").on('input', function() {
            nameerror.text('');
        });
    
    
        $("#email").on('input', function() {
            emailerror.text('');
        });
    
        $("#current-password").on('input', function() {
            passworderror.text('');
        });
        $("#checkbox").on('change', function() {
            checkerror.text('');
        });
        if (fname === '') {
            nameerror.text('Name is required').css('color', 'red');
        }
        if (email === '') {
            emailerror.text('Email is required').css('color', 'red');
        } else {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailPattern.test(email)) {
                emailerror.text('Please enter a valid email address').css('color', 'red');
            } else {
                emailerror.text('');
            }
        }
        if (mpassword === '') {
            passworderror.text('Password is required').css('color', 'red');
        }else if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+{}:;<>,.?~\\/-])[A-Za-z\d!@#$%^&*()_+{}:;<>,.?~\\/-]{8,}$/.test(mpassword)) {
            passworderror.text('Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character').css('color', 'red');
        }
        else {
            passworderror.text('');
        }
        if (!checkbox.is(':checked')) { 
            checkerror.text('You must agree to the terms and conditions').css('color', 'red');
        }
        if (nameerror.text() !== '' || emailerror.text() !== '' || passworderror.text() !== '' || checkerror.text() !== '') {
            return false;
        }
        let formData = {
            'action': 'register_user',
            'name': fname,
            'email': email,
            'password': mpassword,
            'form': 'signup',
        };
        
        $.ajax({
            url: ajax_object.ajaxurl, 
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response){
                    if (response.success) {
                        window.location.href = response.data.url;
                    } else {
                        $("#response").text(response.data.messages);
                    }
                } else {
                    $("#response").text('Invalid response from the server.');
                }
            },
            error: function(xhr) {
                    $("#response").text(xhr.statusText || 'An error occurred. Please try again.').css('color', 'red');      
            }
        });
    });
    jQuery(document).ready(function($) {
        $("#login").click(function(e) {
            e.preventDefault();
            let email = $("#email").val();
            let password = $("#current-password").val();
            let emailError = $("#email-error");
            let passwordError = $("#password-error");
            let responseMessage = $("#response");

            emailError.text('');
            passwordError.text('');
            responseMessage.text('');
    
            
            if (email === '') {
                emailError.text('Email is required').css('color', 'red');
                
            }
            $("#email").on('input', function() {
                $("#email-error").text('');
                $("#response").text('');
            });
        
            $("#current-password").on('input', function() {
                $("#password-error").text(''); 
                $("#response").text('');
            });
        
            if (password === '') {
                passwordError.text('Password is required').css('color', 'red');
                return;
            }

            let formData = {
                action: 'login_user',
                email: email,
                password: password,
            };
            $.ajax({
                url: ajax_object.ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {

                    if (response) {
                        if (response.success) {
                            window.location.href = response.data.url;
                        } else {
                            $("#response").text(response.data);
                        }
                    } else {
                        responseMessage.text('Invalid response from the server.').css('color', 'red');
                    }
                },
                error: function(xhr) {
                    responseMessage.text(xhr.statusText || 'An error occurred. Please try again.').css('color', 'red');
                }
            });
        });
        $("#signup2").click(function(e) {
            e.preventDefault();
            window.location.href = "/registration"; 
        });
    });
});    