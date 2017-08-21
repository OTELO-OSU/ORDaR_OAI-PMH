# ORDaR_OAI-PMH
OAI-PHM implementation



**Installation** 

  Cette application OAI-PMH requiert l'installation de l'entrepot de données
  https://github.com/OTELO-OSU/ORDaR

  Elle utilise ElasticSearch.

  Il faut installer PHP et PHP CURL:

    sudo apt-get install  php5.6

    sudo apt-get install php5.6-curl

Clone du projet:
  
    git clone https://github.com/OTELO-OSU/ORDaR_OAI-PMH.git
  
 Configuration apache:
    
    on active mod_rewrite :
      sudo a2enmod rewrite

     Exemple:
     DocumentRoot /var/www/html/ORDaR_OAI-PMH/Backend/src

    <Directory /var/www/html/ORDaR_OAI-PMH/Backend/src>
        AllowOverride All
        Order allow,deny
        allow from all
    </Directory>


  On redemarre le serveur apache2.


**Fichier de configuration de l'application**

    REPOSITORY_NAME=TEST        #Nom du repository
    BaseUrl=http://test.fr      #URL du repository
    ProtocolVersion=2.0         #Version du protocole OAI-PMH
    adminEmail=admin@admin.fr   #Email de l'administrateur
    deletedRecord="no"          
    granularity=YYYY-MM-DD      #granularité
    TokenGenerationKey="test"   #Clé a utiliser pour chiffrer les resumptionTokens

    #ELASTICSEARCH config
    APIHost=localhost           #Adresse de l'api elasticsearch
    APIPort=9200                #Port de l'api elasticsearch
    authSource=ORDaR            #Nom de la Base 


**Liste des verbs:**

  • Identify : Informations sur l'entrepôt de données.	
  
  • ListMetadataFormats :Demande la liste des formats de métadonnées disponibles.
  
  • ListSets : Demande la liste des ensembles disponibles sur un entrepôt.	
       
       arguments: 
              from : date de début
              until : date de fin
              metadataPrefix
              set
              resumptionToken
  
  • ListIdentifiers :Récupère la liste des identifiants disponibles.
  
  • ListRecords :Retourne une liste d'enregistrements correspondant aux différents paramètres (dates, ensemble) demandés.	Par pages de 10 documents, Utilisez le ResumptionToken pour les parcourir toutes. arguments: resumptionToken
        
     arguments: 
         from : date de début
         until : date de fin
         metadataPrefix
         set
         resumptionToken
  • GetRecord :Récupération d'un enregistrement donné.	
      
      arguments:
      identifier (id du document obligatoire)
      metadataPrefix (Format voulu obligatoire)



