<template>
  <div>
    <b-row>
      <b-col cols="12" v-if="mesa">
        <b-card v-if="pedido && pedido != null" :header="cardHeader" tag="article" class="m-3 mt-3" border-variant="primary" header-bg-variant="primary">
          <b-row class="mt-1 mb-2">
            <b-col cols="12">
              <b-form-group>
                <b-btn
                    v-if="pedido && pedido.editablepedido == 'NO' && pedido.idestado == '5' && pedido.descripcionRolSesion != 'MESERO'"
                    class="ml-3 mb-3 float-right"
                    @click.stop="marcarComoPago()"
                    variant="primary">Marcar como pago</b-btn>
                <b-btn
                    v-if="pedido && pedido.editablepedido == 'SI'"
                    class="ml-3 mb-3 float-right"
                    @click.stop="anularPedido()"
                    variant="primary">Anular Pedido</b-btn>
                <b-btn
                  v-if="pedido && pedido.editablepedido == 'SI'"
                  class="ml-3 mb-3 float-right"
                  @click.stop="abrirModalCambiarMesa()"
                  variant="primary">Cambiar de Mesa</b-btn>
                  <b-btn
                    v-if="(pedido && pedido.editablepedido == 'SI') || (pedido.descripcionestado == 'FACTURADO' && pedido.descripcionRolSesion != 'MESERO')"
                    class="ml-3 mb-3 float-right"
                    @click.stop="cargarFormulario(null,'Agregar')"
                    variant="primary">Agregar Producto</b-btn>         
                <b-btn
                  v-if="(pedido && pedido.editablepedido == 'SI') && (items && items.length > 0)"
                  class="ml-3 mb-3 float-right"
                  @click.stop="enviarPedido()"
                  variant="primary"><template v-if="(pedido && pedido.idestado > 1)"> Volver a </template>Enviar Pedido</b-btn>
                <b-btn
                  v-if="(pedido && pedido.descripcionestado == 'FACTURADO') || ((pedido && pedido.editablepedido == 'SI') && (items && items.length > 0) && (pedido && pedido.idestado > 1))"
                  class="ml-3 mb-3 float-right"
                  @click.stop="facturarPedido()"
                  variant="primary">Generar Factura</b-btn>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row align-h="center">
            <b-col class="contenedor-tabla">
              
              <b-form-checkbox
                  id="checkbox-0"
                  v-model="facturar"
                  name="checkbox-0"
                  value="SI"
                  unchecked-value="NO"
                  class="mb-3"
                >  ¿Desea facturar este pedido?
                </b-form-checkbox>

              <b-card
                title=""
                tag="article"
                class="mb-2"
              >
                <b-container>

                  <label>Seleccione el tipo de pago:</label>
                  <b-form-select v-if="pedido" v-model="pedido.tipopago" class="mb-3">
                    <option value="EFECTIVO">EFECTIVO</option>
                    <option value="TARJETA">TARJETA</option>
                  </b-form-select>
                  
                  <b-form-checkbox
                    id="checkbox-1"
                    v-model="asociarCliente"
                    name="checkbox-1"
                    value="true"
                    unchecked-value="false"
                    class="mb-3"
                  >
                    ¿Desea asociar un cliente a este pedido?
                  </b-form-checkbox>
                  <template v-if="asociarCliente == 'true'">
                    <b-row class="mb-3">
                      <b-col
                        class="text-left"
                        sm="6">
                        <span class="font-weight-bold">
                          <span style="color:red;">* </span>Telefono
                        </span>:<br>
                        <b-form-input
                          type="number"
                          required
                          v-model="pedido.telefonocliente"
                          class="form-control"/>
                      </b-col>

                      <b-col
                        sm="6"
                        class="text-left sm-6">
                        <span class="font-weight-bold">
                          <span style="color:red;">* </span>Nombre
                        </span>:<br>
                        <b-form-input
                          type="text"
                          required
                          v-model="pedido.nombrecliente"
                          class="form-control"/>
                      </b-col>

                      <b-col
                        sm="12"
                        class="text-left sm-12">
                        <span class="font-weight-bold">
                          <span style="color:red;">* </span>Dirección
                        </span>:<br>
                        <b-form-textarea
                          id="textarea"
                          v-model="pedido.direccioncliente"
                          rows="3"
                          max-rows="6"
                        ></b-form-textarea>
                      </b-col>
                    </b-row>
                  </template>
                </b-container>

                <b-btn
                  class="ml-3 mb-3 float-right"
                  @click.stop="guardarCliente"
                  variant="primary">Guardar Datos</b-btn>
              </b-card>

              <b-table
                v-if="items && items.length > 0"
                stacked="sm"
                striped
                hover
                class="columna-centrada"
                tbody-class="columna-centrada"
                :fields="campos"
                :items="items">
                <template slot="acciones" slot-scope="row">
                  <b-button
                    style="margin: 1px;"
                    class="ml-2"
                    @click.stop="cargarFormulario(row.item,'Ver')">
                    <i class="icon-eye"></i>
                  </b-button>
                  <b-button
                    v-if="(pedido && pedido.editablepedido == 'SI') || (pedido.descripcionestado == 'FACTURADO' && pedido.descripcionRolSesion != 'MESERO')"
                    style="margin: 1px;"
                    class="ml-2"
                    variant="primary"
                    @click.stop="cargarFormulario(row.item,'Modificar')">
                    <i class="icon-pencil"></i>
                  </b-button>
                  <b-button
                    v-if="(pedido && pedido.editablepedido == 'SI') || (pedido.descripcionestado == 'FACTURADO' && pedido.descripcionRolSesion != 'MESERO')"
                    style="margin: 1px;"
                    class="ml-2"
                    variant="danger"
                    @click.stop="cargarFormulario(row.item,'Eliminar')">
                    <i class="fa fa-trash"></i>
                  </b-button>
                </template>
                <template slot="descripcionprod" slot-scope="row">
                  <p v-html="row.item.descripcionprod"></p>
                </template>
              </b-table>
              <b-row v-if="items && items.length > 0" class="mt-1 mb-2 text-center">
                <b-col cols="12">
                  <b-alert show><strong>TOTAL: {{totalPedido()}}</strong></b-alert>
                </b-col>
              </b-row>
              <b-row v-else class="mt-1 mb-2 text-center">
                <b-col cols="12">
                  <b-alert show>El pedido está creado, pero no se encontraron registros de productos pedidos. Haz click en el boton Nuevo para agregar productos.</b-alert>
                  <b-btn
                    class="ml-3 float-right"
                    @click.stop="anularPedido()"
                    variant="primary">Anular Pedido</b-btn>
                </b-col>
              </b-row>
            </b-col>
          </b-row>

        </b-card>
        <b-card v-else :header="cardHeader" tag="article" class="m-3 mt-3" border-variant="primary" header-bg-variant="primary">
          <b-row class="mt-1 mb-2 text-center">
            <b-col cols="12">
              <b-alert show variant="info">No ha creado el pedido. Haz click en el boton Crear Pedido para iniciar.</b-alert>
               <b-btn
                class="ml-3 float-right"
                @click.stop="crearPedido()"
                variant="primary"
              >Crear Pedido</b-btn>
            </b-col>
          </b-row>
         
        </b-card>  
      </b-col>
      <b-col cols="12" v-else>
        <b-card tag="article" class="m-3 mt-3">
          <b-row class="mt-1 mb-2">
            <b-col cols="12">
              <b-alert show variant="danger">No ha seleccionado mesa.</b-alert>
            </b-col>
          </b-row>

        </b-card>
      </b-col>
    </b-row>

    <b-modal v-if="objeto" centered v-model="showModal" :title="tipoOperacion" size="lg">
      <b-container>
        <b-form-checkbox
          v-model="isPizza"
          name="checkbox-is-pizza"
          :v-model="isPizza"
          :unchecked-value="false"
          :disabled="tipoOperacion == 'Eliminar' || tipoOperacion == 'Ver' || pedido.descripcionRolSesion == 'HELADOS'"
          class="mb-3"
        >
          ¿ Es Pizza ?
        </b-form-checkbox>
        <template v-if="isPizza">
          <b-form-group label="Tamaño">
            <b-form-radio-group
                id="radio-group-size"
                v-model="sizePizza"
                name="radio-size-component"
                :disabled="tipoOperacion == 'Eliminar' || tipoOperacion == 'Ver'"
              >
              <b-form-radio name="size-radios" value="GRANDE">GRANDE</b-form-radio>
              <b-form-radio name="size-radios" value="PEQUEÑA">PEQUEÑA</b-form-radio>
              <b-form-radio name="size-radios" value="PORCIÓN">PORCIÓN</b-form-radio>
            </b-form-radio-group>
          </b-form-group>
          <b-form-group label="Fracciones">
            <b-form-radio-group
                id="radio-group-fraccion"
                v-model="fraccionPizza"
                name="radio-fraccion-component"
                :disabled="sizePizza == 'PORCIÓN' || tipoOperacion == 'Eliminar' || tipoOperacion == 'Ver'"
              >
              <b-form-radio name="fraccion-radios" value="ENTERA">ENTERA</b-form-radio>
              <b-form-radio name="fraccion-radios" value="MITAD">MITAD Y MITAD</b-form-radio>
            </b-form-radio-group>
          </b-form-group>
        </template>
        <b-row class="mb-3" v-if="fraccionPizza == 'MITAD' && isPizza">
          <b-col
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              FRACCIÓN 1
            </span>:<br>
          </b-col>
          <b-col
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              FRACCIÓN 2
            </span>:<br>
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;"> </span>Tipo Producto
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.descripciontipoproducto}}
            </span>
            <b-form-select v-else v-model="objeto.idtipoproducto" class="mb-3">
              <option :key="'tipo_' + t" v-for="(tipo, t) in filtrarTipoProductos(tipoProductos)" :value="tipo.id">{{tipo.descripcion}}</option>
            </b-form-select>
          </b-col>
          <b-col
            v-if="fraccionPizza == 'MITAD' && isPizza"
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;"> </span>Tipo Producto
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.descripciontipoproducto2}}
            </span>
            <b-form-select v-else v-model="objeto.idtipoproducto2" class="mb-3">
              <option :key="'tipo2_' + t" v-for="(tipo, t) in filtrarTipoProductos(tipoProductos)" :value="tipo.id">{{tipo.descripcion}}</option>
            </b-form-select>
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;">* </span>Producto
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.descripcionproducto}}
            </span>
            <b-form-select v-else v-model="objeto.idproducto" class="mb-3">
              <template v-for="(tipo, t) in tipoProductos">
                  <template v-if="objeto && objeto.idtipoproducto">
                    <template v-for="(producto, p) in tipo.productos">
                      <option v-if="producto.idtipoproducto == objeto.idtipoproducto" :key="'prod_' + t + '_' + p" :value="producto.id">{{producto.descripcion}}</option>
                    </template>
                  </template>
                  <template v-else>
                    <template v-for="(producto, p) in tipo.productos">
                      <option :key="'prod_' + t + '_' + p" :value="producto.id">{{producto.descripcion}}</option>
                    </template>
                  </template>
              </template>
            </b-form-select>
          </b-col>
          <b-col
            v-if="fraccionPizza == 'MITAD' && isPizza"
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;">* </span>Producto 2
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.descripcionproducto2}}
            </span>
            <b-form-select v-else v-model="objeto.idproducto2" class="mb-3">
              <template v-for="(tipo, t) in tipoProductos">
                <template v-if="objeto && objeto.idtipoproducto2">
                  <template v-for="(producto, p) in tipo.productos">
                    <option v-if="producto.idtipoproducto == objeto.idtipoproducto2" :key="'prod2_' + t + '_' + p" :value="producto.id">{{producto.descripcion}}</option>
                  </template>
                </template>
                <template v-else>
                  <template v-for="(producto, p) in tipo.productos">
                    <option :key="'prod2_' + t + '_' + p" :value="producto.id">{{producto.descripcion}}</option>
                  </template>
                </template>
              </template>
            </b-form-select>
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col
            cols="6"
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;">* </span>Cantidad
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.cantidadproducto}}
            </span>
              <b-form-select
                v-else
                v-model="objeto.cantidadproducto"
                :options="options">
              </b-form-select>
          </b-col>
        </b-row>
        <b-row class="mb-3">
          <b-col
            class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;"> </span>Observación
            </span>:<br>
            <span v-if="(tipoOperacion === 'Ver' || tipoOperacion === 'Eliminar')">
              {{objeto.descripcion}}
            </span>
            <b-form-input
              v-else
              type="text"
              required
              v-model="objeto.descripcion"
              class="form-control"/>
          </b-col>
        </b-row>
      </b-container>
      <div slot="modal-footer" v-if="objeto !== null" class="pull-right">
        <b-btn
          v-if="tipoOperacion === 'Ver'"
          size="sm"
          variant="secondary"
          @click.stop="cerrarFormulario">Cerrar</b-btn>
        <b-btn
          v-if="tipoOperacion === 'Agregar'"
          size="sm"
          variant="primary"
          @click.stop="guardar">Guardar</b-btn>
        <b-btn
          v-if="tipoOperacion === 'Modificar'"
          size="sm"
          variant="primary"
          @click.stop="guardarCambios">Guardar Cambios</b-btn>
        <b-btn
          v-if="tipoOperacion === 'Eliminar'"
          size="sm"
          variant="danger"
          @click.stop="eliminar">Eliminar</b-btn>
      </div>
    </b-modal>


    <b-modal centered v-model="showModalCambiarMesa" title="Cambiar de Mesa">
      <b-container>
        <b-row class="mb-3">
          <b-col class="text-left">
            <span class="font-weight-bold">
              <span style="color:red;"> </span>Seleccione la nueva mesa
            </span>:<br>
            <b-form-select  v-model="idMesaNueva" class="mb-3">
              <template v-for="(mesa, m) in mesasDisponibles" >
                <option :key="'mesa_' + m" :value="mesa.id" v-if="mesa.descripcionestadopedido == null">{{mesa.descripcion}}</option>
              </template>
            </b-form-select>
          </b-col>
        </b-row>
      </b-container>
      <div slot="modal-footer" class="pull-right">
        <b-btn
          variant="primary"
          @click.stop="cambiarMesa">Cambiar de Mesa</b-btn>
      </div>
    </b-modal>

  </div>
</template>
<style scoped>
.columna-centrada {
  text-align: center;
}
.mesa {
  cursor: pointer;
}
.border-td {
  border: 1px solid #000;
  padding: 5px;
}
</style>

<script>
var self = this;
export default {
  name: "DetallePedido",
  data: function() {
    return {
      pedido: null,
      mesa: this.$route.params.mesa ? this.$route.params.mesa : null,
      idPedido: this.$route.params.idPedido ? this.$route.params.idPedido : null,
      cardHeader: '<strong> Detalles del pedido, ' + (this.$route.params.mesa && this.$route.params.mesa.descripcion ? this.$route.params.mesa.descripcion : '') + ' ( ' + (this.pedido && this.pedido.descripcionestado ? this.pedido.descripcionestado : '') + ' )' + ' ( ' + (this.pedido && this.pedido.prefijofactura ? this.pedido.prefijofactura : '') + '-' + (this.pedido && this.pedido.numerofactura ? this.pedido.numerofactura : '') + ' )' + ' </strong>',
      showModal: false,
      showModalCambiarMesa: false,
      tipoProductos: [],
      tipoOperacion: null,
      campos: [
        {
          key: "cantidadproducto",
          label: "Cantidad",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        /* {
          key: "descripciontipoproducto",
          label: "Tipo producto",
          sortable: true,
          thStyle: "text-align:center;"
        }, */
        {
          key: "descripcionprod",
          label: "Producto",
          sortable: true,
          thStyle: "text-align:center;"
        },
        {
          key: "precioproducto",
          label: "Precio U.",
          sortable: true,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        },
        {
          key: "acciones",
          label: "Acciones",
          sortable: false,
          thStyle: "text-align:center;",
          tdClass: "columna-centrada"
        }
      ],
      items: [],
      objeto: null,
      objetoViejo: null,
      cantidadProductoVieja: 0,
      idMesaNueva: null,
      mesasDisponibles: [],
      nombreCliente: null,
      asociarCliente: false,
      isPizza: true,
      sizePizza: 'GRANDE',
      fraccionPizza: 'ENTERA',
      options: [
        { value: '1', text: '1' },
        { value: '2', text: '2' },
        { value: '3', text: '3' },
        { value: '4', text: '4' },
        { value: '5', text: '5' },
        { value: '6', text: '6' },
        { value: '7', text: '7' },
        { value: '8', text: '8' },
        { value: '9', text: '9' },
        { value: '10', text: '10' }
      ],
      itemsPrint: [
        "LASAÑAS",
        "PIZZAS",
        "PANZEROTTIS",
        "ADICIONALES DE PROTEÍNA",
        "PIZZERITOS",
        "SANGRIA",
        "HELADO",
        "PASTA"
      ],
      facturar: 'SI'
    };
  },
  watch: {
    asociarCliente: function (valor) {
      if (valor === 'false') {
        this.pedido.nombrecliente = '';
        this.pedido.telefonocliente = '';
        this.pedido.direccioncliente = '';
      }
    },
    'pedido.telefonocliente': function (valor) {
      if (valor.length > 5) {
        this.consultarClienteDelPedidoPorTelefono(valor);
      }
    },
    'objeto.idproducto': function (valor) {
      const tipoProducto = this.tipoProductos.find((tipoProducto) => {
        return tipoProducto.id == this.objeto.idtipoproducto;
      });
      if (tipoProducto && tipoProducto.productos) {
        const producto = tipoProducto.productos.find((producto) => {
          return producto.id == this.objeto.idproducto;
        });
        this.objeto.descripcionproducto = producto.descripcion;
      }
    },
    'objeto.idproducto2': function (valor) {
      const tipoProducto = this.tipoProductos.find((tipoProducto) => {
        return tipoProducto.id == this.objeto.idtipoproducto2;
      });
      if (tipoProducto && tipoProducto.productos) {
        const producto = tipoProducto.productos.find((producto) => {
          return producto.id == this.objeto.idproducto2;
        });
        this.objeto.descripcionproducto2 = producto.descripcion;
      }
    },
    sizePizza: function(valor) {
      if (this.tipoOperacion == 'Agregar' || this.tipoOperacion == 'Modificar') {
        this.objeto = {
          id: this.objeto.id,
          idtipoproducto: null,
          idtipoproducto2: null,
          descripciontipoproducto: null,
          descripciontipoproducto2: null,
          idproducto: null,
          idproducto2: null,
          descripcionproducto: null,
          descripcionproducto2: null,
          costoproducto: null,
          cantidadproducto: 1,
          precioproducto: null,
          descripcion: null
        };
      }
      if (valor == 'PORCIÓN') {
        this.fraccionPizza = 'ENTERA';
      }
    },
    fraccionPizza: function(valor) {
      if (this.tipoOperacion == 'Agregar' || this.tipoOperacion == 'Modificar') {
        this.objeto = {
          id: this.objeto.id,
          idtipoproducto: null,
          idtipoproducto2: null,
          descripciontipoproducto: null,
          descripciontipoproducto2: null,
          idproducto: null,
          idproducto2: null,
          descripcionproducto: null,
          descripcionproducto2: null,
          costoproducto: null,
          cantidadproducto: 1,
          precioproducto: null,
          descripcion: null
        };
      }
    },
    isPizza: function(valor) {
      if (this.tipoOperacion == 'Agregar' || this.tipoOperacion == 'Modificar') {
        this.objeto = {
          id: this.objeto.id,
          idtipoproducto: null,
          idtipoproducto2: null,
          descripciontipoproducto: null,
          descripciontipoproducto2: null,
          idproducto: null,
          idproducto2: null,
          descripcionproducto: null,
          descripcionproducto2: null,
          costoproducto: null,
          cantidadproducto: 1,
          precioproducto: null,
          descripcion: null
        };
      }
    }
  },
  methods: {
    filtrarTipoProductos(tipoProductos) {
      const textFilter = 'PIZZA';
      const sizeFilter = this.sizePizza;
      const condicion = this.isPizza ? true :  false;
      return tipoProductos.filter((tipoProducto) => {
        return (tipoProducto.descripcion.includes(textFilter) === condicion) && (tipoProducto.descripcion.includes(sizeFilter) === condicion);
      });
    },
    atras: function() {
      this.$router.go(-1)
    },
    listarTiposPedido: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = {
        token: token
      };
      this.$http.post("ws/productotipoproducto/", frm).then(resp => {
        self.tipoProductos = resp.data.tiposProducto;
        self.$loader.close();
      });
    },
    abrirModal: function() {
      this.showModal = true
    },
    cerrarModal: function() {
      this.showModal = false
    },
    cargarFormulario: function(obj, operacion) {
      this.tipoOperacion = operacion;
      this.objeto = {...obj};
      this.objetoViejo = null;
      if (obj === null) {
        this.objeto = {
          id: null,
          idtipoproducto: null,
          idtipoproducto2: null,
          descripciontipoproducto: null,
          descripciontipoproducto2: null,
          idproducto: null,
          idproducto2: null,
          descripcionproducto: null,
          descripcionproducto2: null,
          costoproducto: null,
          cantidadproducto: 1,
          precioproducto: null,
          descripcion: null
        };
      } else {
        this.selectedVisble = obj.visible;
        this.cantidadProductoVieja = obj.cantidadproducto;
        this.isPizza = obj.descripciontipoproducto ? obj.descripciontipoproducto.includes('PIZZAS') : false;
        this.fraccionPizza = obj.descripcionproducto2 ? 'MITAD' : 'ENTERA';

        if (obj.descripciontipoproducto && obj.descripciontipoproducto.includes('GRANDE')) {
          this.sizePizza = 'GRANDE';
        } else if (obj.descripciontipoproducto && obj.descripciontipoproducto.includes('PEQUEÑA')) {
          this.sizePizza = 'PEQUEÑA';
        } else if (obj.descripciontipoproducto && obj.descripciontipoproducto.includes('PORCIÓN')) {
          this.sizePizza = 'PORCIÓN';
        }

        setTimeout(() => {
          this.objeto = {
            id: obj.id,
            idtipoproducto: obj.idtipoproducto,
            idtipoproducto2: obj.idtipoproducto2,
            descripciontipoproducto: obj.descripciontipoproducto,
            descripciontipoproducto2: obj.descripciontipoproducto2,
            idproducto: obj.idproducto,
            idproducto2: obj.idproducto2,
            descripcionprod: obj.descripcionprod,
            descripcionproducto: obj.descripcionproducto,
            descripcionproducto2: obj.descripcionproducto2,
            costoproducto: obj.costoproducto,
            cantidadproducto: obj.cantidadproducto,
            precioproducto: obj.precioproducto,
            descripcion: obj.descripcion
          };

          this.objetoViejo = {
            ...this.objeto,
            // descripciontipoproducto: descripcionTipoProducto
          }
        }, 500);
      }

      this.showModal = true;
    },
    validarCampos: function() {
      if (!this.objeto.idproducto || this.objeto.idproducto < 1) {
        this.$toast.error("Debe seleccionar un producto.");
        return false;
      }
      if (!this.objeto.cantidadproducto || this.objeto.cantidadproducto <= 0) {
        this.$toast.error("Debe de escribir la cantidad.");
        return false;
      }
      return true;
    },
    crearPedido: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = {
        idmesa: self.mesa.id,
        token: token
      };
      self.$loader.open({ message: "Creando ..." });
      self.$http.post("ws/pedido/", frm).then(resp => {
        var respuesta = resp.data;
        self.idPedido = respuesta.id;
        self.consultarPedido(self.idPedido);
        self.$toast.success(resp.data.mensaje);
        self.$loader.close();
      }).catch(resp => {
        self.$loader.close();
        if (resp.data && resp.data.mensaje) {
          self.$toast.error(resp.data.mensaje);
        } else {
          self.$toast.error("No se pudo crear el pedido");
        }
      });
    },
    consultarPedido: function(id) {
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = {
        params: {
          id: id,
          token: token
        }
      };
      self.$loader.open({ message: "Cargando ..." });
      self.$http.get("ws/pedido/", frm).then(resp => {
        var respuesta = resp.data;
        self.pedido = respuesta;
        self.isPizza = self.pedido && self.pedido.descripcionRolSesion == 'HELADOS' ? false : true; 
        self.cardHeader =  '<strong> Detalles del pedido, ' + (this.$route.params.mesa && this.$route.params.mesa.descripcion ? this.$route.params.mesa.descripcion : '') + ' ( ' + (this.pedido && this.pedido.descripcionestado ? this.pedido.descripcionestado : '') + ' )' + ' ( ' + (this.pedido && this.pedido.prefijofactura ? this.pedido.prefijofactura : '') + ' - ' + (this.pedido && this.pedido.numerofactura ? this.pedido.numerofactura : '') + ' )' + ' </strong>';
        self.$loader.close();
        if (self.pedido.telefonocliente && self.pedido.nombrecliente && self.pedido.direccioncliente) {
          self.asociarCliente = 'true';
        } else {
          self.asociarCliente = 'false';
        }
        self.listar();
      }).catch(resp => {
        self.$loader.close();
        if (resp.data && resp.data.mensaje) {
          self.$toast.error(resp.data.mensaje);
        } else {
          self.$toast.error("No se pudo consultar el pedido");
        }
      });
    },
    consultarClienteDelPedidoPorTelefono: function(telefono) {
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = {
        params: {
          telefono: telefono,
          token: token
        }
      };
      self.$loader.open({ message: "Cargando ..." });
      self.$http.get("ws/pedido/consultar_cliente_por_telefono.php", frm).then(resp => {
        var respuesta = resp.data;
        self.$loader.close();
        if (respuesta.telefonocliente && respuesta.nombrecliente && respuesta.direccioncliente) {
          self.pedido.telefonocliente = respuesta.telefonocliente;
          self.pedido.nombrecliente = respuesta.nombrecliente;
          self.pedido.direccioncliente = respuesta.direccioncliente;
        } else {
          self.pedido.nombrecliente = '';
          self.pedido.direccioncliente = '';
        }
      }).catch(resp => {
        self.$loader.close();
        self.pedido.nombrecliente = '';
        self.pedido.direccioncliente = '';
      });
    },
    anularPedido: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: "6",
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Anular",
          "Seguro que desea anular el pedido?",
          function() {
            self.$loader.open({ message: "Anulando ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.$loader.close();
              self.atras();
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo anular el pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    marcarComoPago: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      if (!self.validarGuardarCliente()) {
        return false;
      }
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: "7",
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Anular",
          "Seguro que desea marcar como pago el pedido?",
          function() {
            self.$loader.open({ message: "Guardando cambios ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.$loader.close();
              self.atras();
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo marcar como pago el pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    abrirModalCambiarMesa: function() {
      this.listarMesasDisponibles()
      this.showModalCambiarMesa = true
    },
    cambiarMesa: function() {
      var self = this;
      if (!self.idMesaNueva) {
        self.$toast.error("Debe de seleccionar la mesa nueva");
        return false;
      }
      if (!self.validarGuardarCliente()) {
        return false;
      }
      var token = window.localStorage.getItem("token");
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: self.pedido.idestado,
        idmesa: self.idMesaNueva,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Cambiar de Mesa",
          "Seguro que desea cambiar de mesa?",
          function() {
            self.$loader.open({ message: "Cambiando ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.$loader.close();
              self.atras();
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo anular el pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    enviarPedido: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      if (!self.validarGuardarCliente()) {
        return false;
      }
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: "2",
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Enviar",
          "Seguro que desea enviar el pedido?",
          function() {
            self.$loader.open({ message: "Enviando ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.$loader.close();
              
              const itemsValid = self.items.filter((item) => {
                return (
                  item.descripciontipoproducto.includes(self.itemsPrint[0])
                  || item.descripciontipoproducto.includes(self.itemsPrint[1])
                  || item.descripciontipoproducto.includes(self.itemsPrint[2])
                  || item.descripciontipoproducto.includes(self.itemsPrint[3])
                  || item.descripciontipoproducto.includes(self.itemsPrint[4])
                  || item.descripciontipoproducto.includes(self.itemsPrint[5])
                  || item.descripciontipoproducto.includes(self.itemsPrint[6])
                  || item.descripciontipoproducto.includes(self.itemsPrint[7])
                )
              });

              if (itemsValid.length === 0) {
                self.atras();
                return;
              } else {
                 const itemsCaja = self.items.filter((item) => {
                  return (
                    item.descripciontipoproducto.includes(self.itemsPrint[4])
                    || item.descripciontipoproducto.includes(self.itemsPrint[5])
                    || item.descripciontipoproducto.includes(self.itemsPrint[6])
                  )
                });
                
                if (itemsCaja.length > 0 ) {
                  self.generarTicketPedidoCaja();
                }

                const itemsCocina = self.items.filter((item) => {
                  return (
                    item.descripciontipoproducto.includes(self.itemsPrint[0])
                    || item.descripciontipoproducto.includes(self.itemsPrint[1])
                    || item.descripciontipoproducto.includes(self.itemsPrint[2])
                    || item.descripciontipoproducto.includes(self.itemsPrint[3])
                    || item.descripciontipoproducto.includes(self.itemsPrint[7])
                  )
                });

                if (itemsCocina.length > 0) {
                  self.generarTicketPedidoCocina();
                }
              }
              
              self.atras();
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo enviar el pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    validarGuardarCliente: function () {
      let self = this;
      if (this.asociarCliente === 'true') {
        if (!self.pedido.telefonocliente) {
          self.$toast.error("Debe escribir el número de teléfono del cliente");
          return false;
        }
        if (!self.pedido.nombrecliente) {
          self.$toast.error("Debe escribir el nombre del cliente");
          return false;
        }
        if (!self.pedido.direccioncliente) {
          self.$toast.error("Debe escribir la dirección del cliente");
          return false;
        }
      }
      return true;
    },
    guardarCliente: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      if (!self.validarGuardarCliente()) {
        return false;
      }
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: self.pedido.idestado,
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Guardar",
          "Seguro que desea guardar el cliente al pedido?",
          function() {
            self.$loader.open({ message: "Guardando ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.$loader.close();
              self.consultarPedido(self.pedido.id);
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo guardar el cliente al pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    facturarPedido: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      if (!self.validarGuardarCliente()) {
        return false;
      }
      var frm = {
        id: self.idPedido,
        token: token,
        idestado: "5",
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        facturar: self.facturar,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      };
      this.$alertify
        .confirmWithTitle(
          "Facturar",
          "Seguro que desea facturar el pedido?",
          function() {
            self.$loader.open({ message: "Facturando ..." });
            self.$http.put("ws/pedido/", frm).then(resp => {
              var respuesta = resp.data;
              self.$toast.success(resp.data.mensaje);
              self.generarTicketFactura();
              self.$loader.close();
              self.atras();
            }).catch(resp => {
              self.$loader.close();
              if (resp.data && resp.data.mensaje) {
                self.$toast.error(resp.data.mensaje);
              } else {
                self.$toast.error("No se pudo facturar el pedido");
              }
            });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    listar: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = { params: {
          idpedido: self.pedido.id
        }
      };
      this.$http.get("ws/detallepedido/", frm).then(resp => {
          self.items = resp.data;
          if (self.items && self.items.length > 0) {
            self.totalFilas = self.items.length;
          }
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items de detalles del pedido.");
          }
        });
    },
    guardar: function() {
      if(!this.validarCampos()) {
        return false
      }
      var self = this;
      self.$set(self.objeto, "token", window.localStorage.getItem("token"));
      self.$set(self.objeto, "idpedido", this.pedido.id);
      self.$set(self.objeto, "mesa", this.mesa);
      self.$set(self.objeto, "pedido", this.pedido);
      this.$alertify
        .confirmWithTitle(
          "Guardar",
          "Seguro que desea guardar el nuevo registro?",
          function() {
            self.$loader.open({ message: "Guardando ..." });
            self.$http.post("ws/detallepedido/", self.objeto).then(resp => {
                self.$loader.close();
                self.listar();
                self.$toast.success(resp.data.mensaje);
                self.showModal = false;
              })
              .catch(resp => {
                self.$loader.close();
                if (resp.data && resp.data.Error) {
                  self.$toast.error(resp.data.Error);
                } else {
                  self.$toast.error("error registrando");
                }
              });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    guardarCambios: function() {
      if(!this.validarCampos()) {
        return false
      }
      var self = this;
      self.$set(self.objeto, "token", window.localStorage.getItem("token"));
      self.$set(self.objeto, "idpedido", this.pedido.id);
      self.$set(self.objeto, "cantidadproductosumar", (parseFloat(self.cantidadProductoVieja) - parseFloat(self.objeto.cantidadproducto)));
      self.$set(self.objeto, "productoviejo", this.objetoViejo);
      self.$set(self.objeto, "mesa", this.mesa);
      self.$set(self.objeto, "pedido", this.pedido);
      this.$alertify
        .confirmWithTitle(
          "Modificar",
          "Seguro que desea modificar el registro?",
          function() {
            self.showModal = false;
            self.$loader.open({ message: "Guardando Cambios ..." });
            self.$http.put("ws/detallepedido/", self.objeto).then(resp => {
                self.$loader.close();
                self.listar();
                self.$toast.success(resp.data.mensaje);
              })
              .catch(resp => {
                self.$loader.close();
                self.showModal = true;
                if (resp.data && resp.data.mensaje) {
                  self.$toast.error(resp.data.mensaje);
                } else {
                  self.$toast.error("error modificando");
                }
              });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    eliminar: function() {
      var self = this;
      let token = window.localStorage.getItem("token");
      self.$set(self.objeto, "token", window.localStorage.getItem("token"));
      self.$set(self.objeto, "mesa", this.mesa);
      self.$set(self.objeto, "pedido", this.pedido);
      this.$alertify
        .confirmWithTitle(
          "Eliminar",
          "Seguro que desea eliminar el registro?",
          function() {
            self.showModal = false;
            self.$loader.open({ message: "Eliminando ..." });
            self.$http.delete("ws/detallepedido/", {params: {id: self.objeto.id, token: token}, data: self.objeto }).then(resp => {
                self.$loader.close();
                self.listar();
                self.$toast.success(resp.data.mensaje);
              })
              .catch(resp => {
                self.$loader.close();
                self.showModal = true;
                if (resp.data && resp.data.mensaje) {
                  self.$toast.error(resp.data.mensaje);
                } else {
                  self.$toast.error("error eliminando");
                }
              });
          },
          function() {}
        )
        .set("labels", { ok: "Aceptar", cancel: "Cancelar" });
    },
    cerrarFormulario: function() {
      this.showModal = false;
    },
    actualizarTabla: function(itemsFiltrados) {
      // this.totalFilas = itemsFiltrados.length;
      // this.paginaActual = 1;
    },
    generarTicketPedidoCaja: function() {
      var self = this;
      let token = window.localStorage.getItem("token");
      var frm = { 
        productos: self.items,
        mesa: self.mesa,
        token: token,
        idmesero: self.pedido.idmesero
      }
      self.$loader.open({ message: "Generando ..." });
      self.$http.post("ws/ticket/pedido-caja.php", frm).then(resp => {
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          self.$toast.error("error generando ticket de pedido caja");
        });
    },
    generarTicketPedidoCocina: function() {
      var self = this;
      let token = window.localStorage.getItem("token");
      var frm = { 
        productos: self.items,
        mesa: self.mesa,
        token: token,
        idmesero: self.pedido.idmesero
      }
      self.$loader.open({ message: "Generando ..." });
      self.$http.post("ws/ticket/pedido-cocina.php", frm).then(resp => {
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          self.$toast.error("error generando ticket de pedido comandas");
        });
    },
    generarTicketFactura: function() {
      var self = this;
      var token = window.localStorage.getItem("token");
      var frm = { 
        productos: self.items,
        mesa: self.mesa,
        cliente: self.nombreCliente,
        mesero: self.pedido.mesero,
        id: self.idPedido,
        token: token,
        idmesa: self.pedido.idmesa,
        nombrecliente: self.pedido.nombrecliente,
        telefonocliente: self.pedido.telefonocliente,
        direccioncliente: self.pedido.direccioncliente,
        tipopago: self.pedido.tipopago,
        facturar: self.facturar,
        numerofactura: self.pedido.numerofactura,
        prefijofactura: self.pedido.prefijofactura
      }
      self.$loader.open({ message: "Generando ..." });
      self.$http.post("ws/ticket/factura.php", frm).then(resp => {
          self.$loader.close();
          // self.$toast.success(resp.data.mensaje);
          // self.$toast.success("Exito");
        })
        .catch(resp => {
          self.$loader.close();
          self.$toast.error("error generando ticket de factura");
        });
    },
    totalPedido: function() {
      var self = this;
      var total = 0;
      this.items.forEach(item => {
        total += (parseInt(item.precioproducto) * parseFloat(item.cantidadproducto));
      });
      var total = '$' + Number(total.toFixed(1)).toLocaleString();
      return total;
    },
    listarMesasDisponibles: function() {
      this.$loader.open({ message: "Cargando ..." });
      var self = this;
      var frm = { params: {
        }
      };
      this.$http.get("ws/mesa/", frm).then(resp => {
          self.mesasDisponibles = resp.data;
          self.$loader.close();
        })
        .catch(resp => {
          self.$loader.close();
          if (resp.data && resp.data.mensaje) {
            self.$toast.error(resp.data.mensaje);
          } else {
            self.$toast.error("No se pudo obtener los items de las mesas disponibles.");
          }
        });
    },
  },
  created: function() {
    if (this.idPedido) {
      this.consultarPedido(this.idPedido);
    }
    this.listarTiposPedido();
  },
  mounted: function() {
    this.$loader.close();
  }
};
</script>
