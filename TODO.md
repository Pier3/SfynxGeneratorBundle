* strategy pour la command dans le swagger (si on prend la VO pere ou chaque champ)
* faire un objet parseur de swagger (avec d'autres strategies possibles (xml,json))
* x-database dans le swagger
* les VO sont générés meme s'ils existent dans dddbundle
* rename some "bundle" into "structure"
* move the handlers 
* ajouter le getSwaggerFile dans le Query controller comme dans le corps
* faire la génération du fichier config/swagger.yml (déjà commencé)
* ajouter les proceessor monolog (cf etienne)
* separate commands and queries files (manager, yml)
* move "Entity files" in Domain, not in Application
* dans l'entité, refaire le constructeur qui a été maj car on ne peut pas setter l'id
