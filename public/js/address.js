document.addEventListener('DOMContentLoaded', function() {
    var postalCodeInput = document.getElementById('company_postal_code');

    postalCodeInput.addEventListener('change', function() {
        var postalCode = this.value;

        fetch('https://zipcloud.ibsnet.co.jp/api/search?zipcode=' + postalCode)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.results) {
                    var result = data.results[0];
                    document.getElementById('company_prefecture').value = result.address1;
                    document.getElementById('company_city').value = result.address2;
                    document.getElementById('company_address').value = result.address3;
                } else {
                    console.log('該当する郵便番号が見つかりませんでした');
                }
            })
            .catch(function() {
                console.log('通信に失敗しました');
            });
    });
});
