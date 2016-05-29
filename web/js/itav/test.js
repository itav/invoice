function ClassObject(_options) {

    (function () {
        console.log("init ... ");

    })();
    
    
    
    this.publicMethod = function(){
        console.log("form public method");
    };
        
    var privateProperty = 'Private prop';
    
    this.publicProperty = "Public property";
    
    var privateMethod = function(){
        console.log('Private method');
    };
       
    var __constructor = function (_this) {
        privateMethod();
        console.log(privateProperty);
        return _this;
    }(this);
}