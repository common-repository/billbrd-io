function blbrdRecruit() {
    let script_url = "https://billbrd.io/recruitment";

    function showRecruitmentModal() {
        if (blbrd_cr_vars.email.includes("@")) {
            window.recruitment_obj.params = {
                id : encodeURIComponent(blbrd_cr_vars.id),
                domain : encodeURIComponent(blbrd_cr_vars.domain),
                customer_first_name : encodeURIComponent(blbrd_cr_vars.first_name),
                customer_last_name : encodeURIComponent(blbrd_cr_vars.last_name),
                customer_email : encodeURIComponent(blbrd_cr_vars.email),
                promotion_type : blbrd_cr_vars.promotion_type,
                commission : blbrd_cr_vars.commission,
                clearing : blbrd_cr_vars.clearing,
                cookie_window : blbrd_cr_vars.cookie_window,
                code_type : blbrd_cr_vars.code_discount_type,
                code_amount : blbrd_cr_vars.code_discount_amount,
                free_shipping : blbrd_cr_vars.code_free_shipping,
                min_order_amt : blbrd_cr_vars.code_min_order_amount,
                expiry : blbrd_cr_vars.code_expiry,
                usage_limit : blbrd_cr_vars.code_usage_limit,
                currency : blbrd_cr_vars.currency,
                loc : blbrd_cr_vars.loc,
                color : blbrd_cr_vars.color,
                font_color: blbrd_cr_vars.font_color
            };
            window.recruitment_obj.showPopup();
        }
    }

    function loadRecruitmentScript(url, scriptLoadedCallback) {
        let recruitment_script = document.createElement("script");
        recruitment_script.type = "text/javascript";
        if (recruitment_script.readyState) {
            recruitment_script.onreadystatechange = function() {
                if (recruitment_script.readyState == "loaded" || recruitment_script.readyState == "complete") {
                    recruitment_script.onreadystatechange = null;
                    scriptLoadedCallback();
                }
            }
        }
        else {
            recruitment_script.onload = function() {
                scriptLoadedCallback();
            }
        }
        recruitment_script.src = url;
        document.body.appendChild(recruitment_script);
    }

    loadRecruitmentScript(script_url, showRecruitmentModal);
};

(function check_ready() { document.body && window.tracking_obj ? blbrdRecruit() : setTimeout(check_ready, 5);})();
