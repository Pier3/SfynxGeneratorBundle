* Strategy pour la command dans le swagger (si on prend la VO pere ou chaque champ)
* Faire un objet parseur de swagger (avec d'autres strategies possibles (xml,json))
  * Object: OK. Développer parseur XML et JSON 
* X-database dans le swagger
* Les VO sont générés meme s'ils existent dans dddbundle
* Rename some "bundle" into "structure"
* Ajouter le getSwaggerFile dans le Query controller comme dans le corps
* Faire la génération du fichier config/swagger.yml (déjà commencé)
* Ajouter les proceessor monolog (cf etienne)
* Dans l'entité, refaire le constructeur qui a été maj car on ne peut pas setter l'id
* Ajout d'un service Route Loader dont la fonctionnalité est de charger les fichiers routes, et ceci afin d'éviter avoir à ajouter à la main dans app/config/routin.yml les fichier
