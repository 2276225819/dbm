<?php 
include __DIR__."/../vendor/autoload.php";


#Conn->execute(STRING[,...args]); #PDOStatement
#Conn->lastInsertId();            #int
#Conn->debug;                     #bool
#Conn->sql(TABLE,...PKS)          #SQL 
#Conn->scope();                   #Transaction

#SQL[INDEX]                 #Row/NULL
#SQL[MODEL]                 #Sql/NULL
#SQL[FIELD]                 #mixed/NULL
#SQL->get(INDEX)            #Row/Throw
#SQL->val()                 #Row
#SQL->val(FIELD)            #mixed
#SQL->getIterator()         #iterator()=>Row
#SQL->getAllIterator()      #iterator()=>array
#SQL->each(Model=>closer)   #array
#SQL->map(Model=>closer)    #Sql
#SQL->all()                 #[array,array...]/[]  
#SQL->all(KEY)              #[VALUE,VALUE...]/[]  
#SQL->keypair(KEY)          #[KEY=>array,KEY=>array...]/[]
#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[] 

#SQL(...PKV)                	  #SQL
#SQL->find(...PKV)          	  #SQL
#SQL->where(STRING[, ..._args]);  #SQL
#SQL->where(ARRAY);               #SQL
#SQL->and(STRING[, ..._args]);    #SQL
#SQL->and(ARRAY);                 #SQL
#SQL->or(STRING[, ..._args]);     #SQL
#SQL->or(ARRAY);                  #SQL
#SQL->order(STRING[,..._args]);   #SQL
#SQL->field(STRING);              #SQL
#SQL->limit(INT[,INT])            #SQL

#SQL->insert(ARRAY);             #MODLE  
#SQL->insertMulit(ARRAY_LIST);   #int
#SQL->update(ARRAY);             #int
#SQL->delete();                  #int
#SQL->set(ARRAY);				 #SQL
#SQL->ref(TABLE,PKS,REF)         #SQL

#ROW[FIELD]                  #MIXED/NULL 
#ROW[MODEL]                  #Sql/NULL 
#ROW->val(FIELD)             #MIXED
#ROW->ref(TABLE,PKS,REF)     #SQL
#ROW->create()               #bool
#ROW->save([PKS])            #bool
#ROW->destroy([PKS])         #bool

 

