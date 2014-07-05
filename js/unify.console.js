var UnifyConsole = {
  runBot: function(btn, id) {
    ajax.post("/unify/console/runBot/id/"+ id, {}, {
      showProgress: function() {
        lockButton(btn);
      },
      hideProgress: function() {
        unlockButton(btn);
      },
      onDone: function() {
        boxPopup('Команда исполнена успешно');
      }
    });
  }
};

try{stManager.done('unify.console.js');}catch(e){}